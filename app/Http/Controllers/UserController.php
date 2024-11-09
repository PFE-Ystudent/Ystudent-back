<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRequest;
use App\Http\Requests\UserEditResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserAccountResource;
use App\Http\Resources\UserDetailsResource;
use App\Http\Traits\IndexTrait;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use IndexTrait;

    public function me () {
        $user = Auth::user();

        return response()->json(UserAccountResource::make($user));
    }

    public function show (User $user) {
        $user->loadCount(['posts', 'postReplies']);
        
        return response()->json(UserDetailsResource::make($user));
    }

    public function edit (UserEditResource $request) {
        $validated = $request->validated();

        /** @var User $user */
        $user = Auth::user();
        $user->update($validated);

        if ($request->file('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $fileName = 'avatar.' . $extension;
            $path = $request->file('avatar')->storeAs('users/' . $user->id, $fileName, 'public');

            $user->avatar = $path;
            $user->save();
        }

        return response()->json(UserAccountResource::make($user));
    }

    public function getPosts(User $user, IndexRequest $indexRequest)
    {
        $pagination = $indexRequest->validated();

        $postsQuery = Post::query()
            ->withDetails()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        $posts = $this->indexQuery($postsQuery, $pagination)->get();
        $lastPage = ceil($postsQuery->count() / $pagination['per_page']);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'lastPage' => $lastPage
        ]);
    }
}
