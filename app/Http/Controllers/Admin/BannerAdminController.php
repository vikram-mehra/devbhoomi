<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BannerAdminController extends Controller
{
    public function index()
    {
        $banners = Banner::where('placement', Banner::PLACEMENT_HOME_SLIDER)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(30);

        return view('admin.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'eyebrow' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:2000',
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'link' => 'nullable|string|max:2048',
            'button_label' => 'nullable|string|max:120',
            'secondary_button_label' => 'nullable|string|max:120',
            'secondary_link' => 'nullable|string|max:2048',
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
                ->withErrors(['image' => __('Could not save the file. Ensure storage/app/public exists and is writable, then run: php artisan storage:link')])
                ->withInput();
        }

        if (! $imageValue) {
            return back()->withErrors(['image' => __('Upload failed.')])->withInput();
        }

        Banner::create([
            'title' => $request->title,
            'eyebrow' => $request->eyebrow,
            'subtitle' => $request->subtitle,
            'image' => $imageValue,
            'link' => $request->link,
            'button_label' => $request->button_label,
            'secondary_button_label' => $request->secondary_button_label,
            'secondary_link' => $request->secondary_link,
            'placement' => Banner::PLACEMENT_HOME_SLIDER,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => true,
        ]);
        $this->flushHomeCaches();

        return back()->with('status', __('Banner created.'));
    }

    public function update(Request $request, Banner $banner)
    {
        $this->ensureHomeSlider($banner);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'eyebrow' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:2000',
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'link' => 'nullable|string|max:2048',
            'button_label' => 'nullable|string|max:120',
            'secondary_button_label' => 'nullable|string|max:120',
            'secondary_link' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);

        $imageValue = $banner->image;

        if ($request->hasFile('image')) {
            $this->deleteStoredBannerFile($banner);
            $imageValue = $request->file('image')->store('banners', 'public');
        }

        if ($imageValue === '' || $imageValue === null) {
            return back()->withErrors(['image' => __('Upload an image file for this banner.')])->withInput();
        }

        $banner->update([
            'title' => $request->title,
            'eyebrow' => $request->eyebrow,
            'subtitle' => $request->subtitle,
            'image' => $imageValue,
            'link' => $request->link,
            'button_label' => $request->button_label,
            'secondary_button_label' => $request->secondary_button_label,
            'secondary_link' => $request->secondary_link,
            'placement' => Banner::PLACEMENT_HOME_SLIDER,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);
        Cache::forget('home.banners');

        return back()->with('status', __('Banner updated.'));
    }

    public function destroy(Banner $banner)
    {
        $this->ensureHomeSlider($banner);
        $this->deleteStoredBannerFile($banner);
        $banner->delete();
        $this->flushHomeCaches();

        return back()->with('status', __('Banner removed.'));
    }

    public function toggleActive(Banner $banner)
    {
        $this->ensureHomeSlider($banner);
        $banner->update(['is_active' => ! $banner->is_active]);
        $banner->refresh();
        $this->flushHomeCaches();

        return back()->with('status', $banner->is_active ? __('Banner is visible.') : __('Banner is hidden.'));
    }

    private function flushHomeCaches(): void
    {
        Cache::forget('home.banners');
        Cache::forget('home.promo_banners');
    }

    protected function deleteStoredBannerFile(Banner $banner): void
    {
        if ($banner->isStoredFile() && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
    }

    private function ensureHomeSlider(Banner $banner): void
    {
        if ($banner->placement !== Banner::PLACEMENT_HOME_SLIDER) {
            abort(404);
        }
    }
}
