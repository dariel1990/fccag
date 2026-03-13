<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('user:id,name')
            ->latest('published_at')
            ->get()
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'category' => $post->category,
                'author' => $post->user?->name,
                'published_at' => $post->published_at?->format('M d, Y'),
            ]);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post): Response
    {
        abort_unless($post->is_published, 404);

        $post->load('user:id,name');

        $related = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'category', 'published_at'])
            ->map(fn (Post $related) => [
                'id' => $related->id,
                'title' => $related->title,
                'slug' => $related->slug,
                'excerpt' => $related->excerpt,
                'category' => $related->category,
                'published_at' => $related->published_at?->format('M d, Y'),
            ]);

        return Inertia::render('Blog/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'category' => $post->category,
                'author' => $post->user?->name,
                'published_at' => $post->published_at?->format('M d, Y'),
            ],
            'related' => $related,
        ]);
    }
}
