<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('market.blog.index', compact('posts'));
    }

    public function show(BlogPost $blogPost)
    {
        if (! $blogPost->is_published) {
            abort(404);
        }
        if ($blogPost->published_at && $blogPost->published_at->isFuture()) {
            abort(404);
        }

        return view('market.blog.show', [
            'post' => $blogPost,
            'relatedPosts' => $this->relatedPosts($blogPost),
        ]);
    }

    protected function relatedPosts(BlogPost $post)
    {
        return BlogPost::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }
}
