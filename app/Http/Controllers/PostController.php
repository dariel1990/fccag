<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('posts/Index', [
            'posts' => Post::query()
                ->with('user:id,name')
                ->latest()
                ->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'category' => $post->category,
                    'is_published' => $post->is_published,
                    'published_at' => $post->published_at?->format('M d, Y'),
                    'author' => $post->user?->name,
                    'created_at' => $post->created_at->format('M d, Y'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('posts/Create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($data['is_published'] ?? false) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $data['user_id'] = $request->user()->id;

        $post = Post::create($data);

        return to_route('blog.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'category' => $post->category,
                'is_published' => $post->is_published,
                'published_at' => $post->published_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (($data['is_published'] ?? false) && ! $post->published_at) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $post->update($data);

        return to_route('blog.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return to_route('blog.index')->with('success', 'Post deleted.');
    }
}
