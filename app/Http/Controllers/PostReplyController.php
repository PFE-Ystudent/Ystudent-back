<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostReplyResource;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\PostReplyFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostReplyController extends Controller
{
    public function index(Post $post)
    {
        $postReplies = PostReply::with('files', 'author')->where('post_id', $post->id)->get();
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

        $postReply->load('author');
    
        return response()->json($postReply, 201);
    }

    public function update(Request $request, PostReply $postReply)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $postReply->update($validated);

        return response()->json($postReply);
    }

    public function destroy(PostReply $postReply)
    {
        $postReply->is_archived = true;
        $postReply->save();

        return response()->json(null, 204);
    }

    public function addFiles(Request $request, PostReply $postReply)
    {
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
}