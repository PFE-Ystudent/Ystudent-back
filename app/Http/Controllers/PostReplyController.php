<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostReplyResource;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\PostReplyFile;
use App\Models\PostReplyUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostReplyController extends Controller
{
    public function index(Post $post)
    {
        $this->authorize('view', $post);

        $postReplies = PostReply::with('files', 'author', 'postReplyUps')
            ->withCount('postReplyUps')
            ->where('post_id', $post->id)
            ->get()->sortByDesc('post_reply_ups_count');

        return response()->json([
            'postReplies' => PostReplyResource::collection($postReplies)
        ]);
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $postReply = new PostReply();
        $postReply->content = $validated['content'];

        $postReply->author()->associate(Auth::user()->id);
        $postReply->post()->associate($post);

        $postReply->save();
    
        return response()->json(PostReplyResource::make($postReply), 201);
    }

    public function update(Request $request, PostReply $postReply)
    {
        $this->authorize('update', $postReply);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $postReply->update($validated);

        return response()->json(PostReplyResource::make($postReply));
    }

    public function destroy(PostReply $postReply)
    {
        $this->authorize('delete', $postReply);

        $postReply->is_archived = true;
        $postReply->save();

        return response()->json(null, 204);
    }

    public function addFiles(Request $request, PostReply $postReply)
    {
        $this->authorize('update', $postReply);

        $validated = $request->validate([
            'files.*' => 'file|mimes:jpg,png,jpeg|max:2048',
        ]);

        $files = [];
        if($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('post_reply_files', 'public');
                $postReplyFile = PostReplyFile::create([
                    'filename' => $file->getClientOriginalName(),
                    'filepath' => $path,
                    'post_reply_id' => $postReply->id,
                ]);
                $files[] = $postReplyFile;
            }
        }

        return response()->json($files, 201);
    }

    public function upVote(PostReply $postReply)
    {
        $this->authorize('view', $postReply);

        $user = Auth::user();
        if ($postReply->isUpVoted) {
            $postReply->postReplyUps()->where('user_id', $user->id)->delete();
        } else {
            $postReplyUp = new PostReplyUp();
            $postReplyUp->user()->associate($user->id);
            $postReplyUp->postReply()->associate($postReply->id);

            $postReplyUp->save();
        }

        return response()->json([
            'upCount' => $postReply->postReplyUps()->count(),
            'isUpVoted' => !$postReply->isUpVoted
        ]);
    }
}