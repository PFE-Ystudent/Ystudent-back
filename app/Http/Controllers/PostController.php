<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['page' => 'integer|min:1']);
        $page = $validated['page'] - 1 ?? 0;

        $posts = Post::query()->with('files', 'author', 'categories')
            ->withCount('replies')
            ->where('user_id', Auth::user()->id)
            ->where('is_archived', false)
            ->orderByDesc('created_at')
            ->skip($page * 5)
            ->limit(5)
            ->get();

        $lastPage = ceil(Post::query()->where('is_archived', false)->where('user_id', Auth::user()->id)->count() / 5);
        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }

    public function followedPost(Request $request)
    {
        $validated = $request->validate(['page' => 'integer|min:1']);
        $page = $validated['page'] - 1 ?? 0;

        $posts = Post::query()->with('files', 'author', 'categories')
            ->withCount('replies')
            ->where('is_archived', false)
            ->orderByDesc('created_at')
            ->skip($page * 5)
            ->limit(5)
            ->get();

        $lastPage = ceil(Post::query()->where('is_archived', false)->count() / 5);
        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }
  
    public function newPost(Request $request)
    {
        $validated = $request->validate(['page' => 'integer|min:1']);
        $page = $validated['page'] - 1 ?? 0;

        $posts = Post::query()->with('files', 'author', 'categories')
            ->withCount('replies')
            ->where('is_archived', false)
            ->orderByDesc('created_at')
            ->skip($page * 5)
            ->limit(5)
            ->get();

        $lastPage = ceil(Post::query()->where('is_archived', false)->count() / 5);
        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'categories' => 'required|array',
            'categories.*' => 'exists:'.(new Category())->getTable().',id',
        ]);

        DB::beginTransaction();
        try {
            $post = new Post($validated);
            $post->author()->associate(Auth::user()->id);
            $post->save();
            $post->categories()->sync($validated['categories']);
            
            DB::commit();
            return response()->json(PostResource::make($post), 201);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Post $post)
    {
        return response()->json([
            'post' => PostResource::make($post)
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $post->is_archived = true;
        $post->save();

        return response()->json(null, 204);
    }

    public function addFiles(Request $request, Post $post)
    {
        $validated = $request->validate([
            'images.*' => 'required|file|mimes:jpg,png,jpeg|max:2048',
        ]);

        $files = [];
        if($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('post_images', 'public');
                $postFile = PostFile::create([
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'post_id' => $post->id,
                ]);
                $files[] = $postFile;
            }
        }

        return response()->json($files, 201);
    }
}
