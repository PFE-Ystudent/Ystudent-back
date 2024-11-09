<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRequest;
use App\Http\Requests\Post\PostIndexRequest;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Http\Traits\IndexTrait;
use App\Models\Post;
use App\Models\PostFile;
use App\Models\Survey;
use App\Models\SurveyOption;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    use IndexTrait;

    public function index(IndexRequest $indexRequest, PostIndexRequest $request)
    {
        $pagination = $indexRequest->validated();
        $validated = $request->validated();

        $postsQuery = Post::query()
            ->withDetails()
            ->where('user_id', Auth::user()->id)
            ->filtered($validated)
            ->orderByDesc('created_at');

        $posts = $this->indexQuery($postsQuery, $pagination)->get();
        $lastPage = ceil($postsQuery->count() / $pagination['per_page']);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }

    public function followedPost(IndexRequest $indexRequest, PostIndexRequest $request)
    {
        return $this->index($indexRequest, $request);
    }
  
    public function newPost(IndexRequest $indexRequest, PostIndexRequest $request)
    {
        $pagination = $indexRequest->validated();
        $validated = $request->validated();

        $postsQuery = Post::query()
            ->withDetails()
            ->filtered($validated)
            ->orderByDesc('created_at');

        $posts = $this->indexQuery($postsQuery, $pagination)->get();
        $lastPage = ceil($postsQuery->count() / $pagination['per_page']);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }

    public function store(PostStoreRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $post = new Post($validated);
            $post->author()->associate(Auth::user()->id);
            $post->save();
            $post->categories()->sync($validated['categories']);

            foreach ($validated['integrations'] as $integration) {
                if ($integration['type'] === 'survey') {
                    $survey = new Survey();
                    $survey->question = $integration['data']['question'];
                    $survey->post()->associate($post);
                    $survey->save();

                    foreach ($integration['data']['options'] as $option) {
                        $surveyOption = new SurveyOption();
                        $surveyOption->name = $option;
                        $surveyOption->survey()->associate($survey);
                        $surveyOption->save();
                    }
                }
            }
            
            DB::commit();
            return response()->json(PostResource::make($post), 201);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        $post->loadDetails();

        return response()->json([
            'post' => PostResource::make($post)
        ]);
    }

    public function update(PostUpdateRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->is_archived = true;
        $post->save();

        return response()->json(null, 204);
    }

    public function addFiles(Request $request, Post $post)
    {
        $this->authorize('update', $post);

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
