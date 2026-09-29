<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostAdminController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderByDesc('published_at')->orderByDesc('id')->paginate(20);

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog-posts.form', ['post' => null]);
    }

    public function store(Request $request)
    {
        $this->assertImageUploaded($request);

        $data = $this->validated($request);
        $slug = $data['slug'] !== '' && $data['slug'] !== null
            ? Str::slug($data['slug'])
            : BlogPost::uniqueSlug($data['title']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog', 'public');
        }

        BlogPost::create([
            'title' => $data['title'],
            'slug' => $slug,
            'meta_title' => $this->nullableSeo($data['meta_title'] ?? null),
            'meta_description' => $this->nullableSeo($data['meta_description'] ?? null),
            'meta_keywords' => $this->nullableSeo($data['meta_keywords'] ?? null),
            'canonical_url' => $this->nullableSeo($data['canonical_url'] ?? null),
            'og_image' => $this->nullableSeo($data['og_image'] ?? null),
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'image' => $imagePath,
            'published_at' => $data['published_at'],
            'is_published' => $data['is_published'],
            'sort_order' => $data['sort_order'],
        ]);
        $this->bustBlogCache();

        return redirect()->route('admin.blog-posts.index')->with('status', __('Blog post created.'));
    }

    public function edit(BlogPost $blogPost)
    {
        return view('admin.blog-posts.form', ['post' => $blogPost]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->assertImageUploaded($request);

        $data = $this->validated($request);
        $slug = $data['slug'] !== '' && $data['slug'] !== null
            ? Str::slug($data['slug'])
            : BlogPost::uniqueSlug($data['title'], $blogPost->id);

        if (BlogPost::where('slug', $slug)->where('id', '!=', $blogPost->id)->exists()) {
            $slug = BlogPost::uniqueSlug($data['title'], $blogPost->id);
        }

        $imageValue = $blogPost->image;
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($blogPost);
            $imageValue = $request->file('image')->store('blog', 'public');
        }

        $blogPost->update([
            'title' => $data['title'],
            'slug' => $slug,
            'meta_title' => $this->nullableSeo($data['meta_title'] ?? null),
            'meta_description' => $this->nullableSeo($data['meta_description'] ?? null),
            'meta_keywords' => $this->nullableSeo($data['meta_keywords'] ?? null),
            'canonical_url' => $this->nullableSeo($data['canonical_url'] ?? null),
            'og_image' => $this->nullableSeo($data['og_image'] ?? null),
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'image' => $imageValue,
            'published_at' => $data['published_at'],
            'is_published' => $data['is_published'],
            'sort_order' => $data['sort_order'],
        ]);
        $this->bustBlogCache();

        return redirect()->route('admin.blog-posts.index')->with('status', __('Blog post updated.'));
    }

    public function destroy(BlogPost $blogPost)
    {
        $this->deleteStoredImage($blogPost);
        $blogPost->delete();
        $this->bustBlogCache();

        return redirect()->route('admin.blog-posts.index')->with('status', __('Blog post deleted.'));
    }

    protected function validated(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string|max:65000',
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:2048',
            'og_image' => 'nullable|string|max:2048',
        ], [
            'image.mimes' => __('Use JPEG, PNG, GIF, or WebP.'),
            'image.max' => __('Image must be 5 MB or smaller.'),
        ]);

        return [
            'title' => $request->title,
            'slug' => $request->input('slug'),
            'excerpt' => $request->excerpt,
            'body' => $this->sanitizeBody($request->body),
            'published_at' => $request->filled('published_at') ? $request->date('published_at') : null,
            'is_published' => $request->boolean('is_published'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'canonical_url' => $request->input('canonical_url'),
            'og_image' => $request->input('og_image'),
        ];
    }

    protected function nullableSeo(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function sanitizeBody(?string $body): string
    {
        $body = trim((string) $body);
        if ($body === '') {
            return '';
        }

        $allowed = '<p><br><h2><h3><h4><ul><ol><li><a><strong><em><b><i><u><blockquote><img><table><thead><tbody><tr><th><td><hr><span><div>';
        $clean = strip_tags($body, $allowed);

        return preg_replace('/\s(on\w+|style|class)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
    }

    protected function deleteStoredImage(BlogPost $post): void
    {
        if (! $post->isStoredFile()) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $post->image), '/');
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $legacy = storage_path('app/public/'.$path);
        if (is_file($legacy)) {
            @unlink($legacy);
        }
    }

    protected function assertImageUploaded(Request $request): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $file = $request->file('image');
        if ($file->isValid()) {
            return;
        }

        $code = $file->getError();
        if ($code === UPLOAD_ERR_OK) {
            return;
        }

        if (in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            $message = __('Cover image is too large. Use an image under 5 MB or increase upload_max_filesize in PHP.');
        } elseif ($code === UPLOAD_ERR_PARTIAL) {
            $message = __('Cover image upload was interrupted. Please try again.');
        } elseif ($code === UPLOAD_ERR_NO_FILE) {
            $message = __('No cover image was received.');
        } else {
            $message = __('Cover image upload failed. Please try again.');
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'image' => $message,
        ]);
    }

    protected function bustBlogCache(): void
    {
        Cache::forget('home.blog_posts');
    }
}
