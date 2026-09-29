<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomePromoAdminController extends Controller
{
    public function index()
    {
        $cards = Banner::where('placement', Banner::PLACEMENT_HOME_PROMO)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.showcase.home-promo', compact('cards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'eyebrow' => 'nullable|string|max:255',
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'link' => 'nullable|string|max:2048',
            'button_label' => 'nullable|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ], [
            'image.required' => __('Choose an image file to upload.'),
            'image.mimes' => __('Use JPEG, PNG, GIF, or WebP.'),
            'image.max' => __('Image must be 5 MB or smaller.'),
        ]);

        try {
            $imageValue = $request->file('image')->store('banners', 'public');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['image' => __('Could not save the file. Run: php artisan storage:link')])
                ->withInput();
        }

        Banner::create([
            'title' => $request->title,
            'eyebrow' => $request->eyebrow,
            'subtitle' => null,
            'image' => $imageValue,
            'link' => $request->link,
            'button_label' => $request->button_label ?: __('Shop now'),
            'secondary_button_label' => null,
            'secondary_link' => null,
            'placement' => Banner::PLACEMENT_HOME_PROMO,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => true,
        ]);

        $this->flushCache();

        return back()->with('status', __('Promo card added.'));
    }

    public function update(Request $request, Banner $banner)
    {
        $this->ensureHomePromo($banner);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'eyebrow' => 'nullable|string|max:255',
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'link' => 'nullable|string|max:2048',
            'button_label' => 'nullable|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);

        $imageValue = $banner->image;

        if ($request->hasFile('image')) {
            $this->deleteStoredFile($banner);
            $imageValue = $request->file('image')->store('banners', 'public');
        }

        $banner->update([
            'title' => $request->title,
            'eyebrow' => $request->eyebrow,
            'image' => $imageValue,
            'link' => $request->link,
            'button_label' => $request->button_label,
            'sort_order' => (int) $request->input('sort_order', $banner->sort_order),
            'is_active' => $request->boolean('is_active'),
            'placement' => Banner::PLACEMENT_HOME_PROMO,
        ]);

        $this->flushCache();

        return back()->with('status', __('Promo card saved.'));
    }

    public function destroy(Banner $banner)
    {
        $this->ensureHomePromo($banner);
        $this->deleteStoredFile($banner);
        $banner->delete();
        $this->flushCache();

        return back()->with('status', __('Promo card removed.'));
    }

    public function toggleActive(Banner $banner)
    {
        $this->ensureHomePromo($banner);
        $banner->update(['is_active' => ! $banner->is_active]);
        $this->flushCache();

        return back()->with('status', $banner->is_active ? __('Card is visible on homepage.') : __('Card is hidden.'));
    }

    private function ensureHomePromo(Banner $banner): void
    {
        if ($banner->placement !== Banner::PLACEMENT_HOME_PROMO) {
            abort(404);
        }
    }

    private function flushCache(): void
    {
        Cache::forget('home.promo_banners');
    }

    private function deleteStoredFile(Banner $banner): void
    {
        if ($banner->isStoredFile() && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
    }
}
