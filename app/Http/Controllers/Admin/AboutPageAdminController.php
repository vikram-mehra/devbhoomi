<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutGalleryItem;
use App\Models\AboutHighlight;
use App\Models\AboutPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutPageAdminController extends Controller
{
    public function edit()
    {
        $page = AboutPage::with(['highlights', 'galleryItems'])->first();
        if (! $page) {
            $page = AboutPage::createDefault();
        }

        return view('admin.pages.about', compact('page'));
    }

    public function update(Request $request)
    {
        $page = AboutPage::with(['highlights', 'galleryItems'])->first();
        if (! $page) {
            $page = AboutPage::createDefault();
        }

        $data = $request->validate([
            'hero_eyebrow' => 'nullable|string|max:120',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:2000',
            'hero_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'story_heading' => 'nullable|string|max:255',
            'story_body' => 'nullable|string|max:50000',
            'story_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'mission_title' => 'nullable|string|max:255',
            'mission_body' => 'nullable|string|max:5000',
            'vision_title' => 'nullable|string|max:255',
            'vision_body' => 'nullable|string|max:5000',
            'gallery_heading' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:2048',
            'og_image' => 'nullable|string|max:2048',
            'is_published' => 'nullable|boolean',
            'highlights' => 'nullable|array|max:8',
            'highlights.*.id' => 'nullable|integer',
            'highlights.*.icon' => 'nullable|string|max:64',
            'highlights.*.label' => 'nullable|string|max:120',
            'highlights.*.value' => 'nullable|string|max:120',
            'highlights.*.sort_order' => 'nullable|integer|min:0|max:65535',
            'gallery_images' => 'nullable|array|max:12',
            'gallery_images.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'gallery_captions' => 'nullable|array',
            'gallery_captions.*' => 'nullable|string|max:255',
        ]);

        $payload = collect($data)->except(['hero_image', 'story_image', 'highlights', 'gallery_images', 'gallery_captions', 'is_published'])->all();
        $payload['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('hero_image')) {
            $this->deleteStoredImage($page->hero_image);
            $payload['hero_image'] = $request->file('hero_image')->store('about', 'public');
        }

        if ($request->hasFile('story_image')) {
            $this->deleteStoredImage($page->story_image);
            $payload['story_image'] = $request->file('story_image')->store('about', 'public');
        }

        $page->update($payload);
        $this->syncHighlights($page, $request->input('highlights', []));

        if ($request->hasFile('gallery_images')) {
            $captions = $request->input('gallery_captions', []);
            $sortBase = (int) $page->galleryItems()->max('sort_order');
            foreach ($request->file('gallery_images') as $i => $file) {
                if (! $file) {
                    continue;
                }
                $path = $file->store('about/gallery', 'public');
                $page->galleryItems()->create([
                    'image' => $path,
                    'caption' => $captions[$i] ?? null,
                    'sort_order' => $sortBase + $i + 1,
                ]);
            }
        }

        AboutPage::flushCache();

        return back()->with('status', __('About page saved.'));
    }

    public function destroyGalleryItem(AboutGalleryItem $aboutGalleryItem)
    {
        $this->deleteStoredImage($aboutGalleryItem->image);
        $aboutGalleryItem->delete();
        AboutPage::flushCache();

        return back()->with('status', __('Gallery image removed.'));
    }

    private function syncHighlights(AboutPage $page, array $rows): void
    {
        $keptIds = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label === '' && $value === '') {
                continue;
            }

            $attrs = [
                'icon' => trim((string) ($row['icon'] ?? 'bi-star')) ?: 'bi-star',
                'label' => $label ?: __('Highlight'),
                'value' => $value ?: '—',
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ];

            if (! empty($row['id'])) {
                $highlight = AboutHighlight::where('about_page_id', $page->id)->where('id', $row['id'])->first();
                if ($highlight) {
                    $highlight->update($attrs);
                    $keptIds[] = $highlight->id;

                    continue;
                }
            }

            $created = $page->highlights()->create($attrs);
            $keptIds[] = $created->id;
        }

        $page->highlights()->whereNotIn('id', $keptIds)->delete();
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && strpos($path, 'http') !== 0) {
            Storage::disk('public')->delete($path);
        }
    }
}
