<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRequest;
use App\Http\Requests\UserEditResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserAccountResource;
use App\Http\Resources\UserDetailsResource;
use App\Http\Resources\UserSelectResource;
use App\Http\Traits\IndexTrait;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use IndexTrait;

    public function me() {
        $user = Auth::user();

        return response()->json(UserAccountResource::make($user));
    }

    public function show(User $user) {
        $user->loadCount(['posts', 'postReplies']);
        /**
         * @var User
         */
        $authUser = Auth::user();
        $userRelation = $authUser->getRelationWith($user);
        $user->setAttribute('relationType', $userRelation->user_relation_type_id ?? null);
        
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

    public function fetchUsers(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string']
        ]);
        $query = $validated['query'];

        $users = User::query()
            ->where('username', 'like', '%' . $query . '%')
            ->limit(100)
            ->get();

        return response()->json(UserSelectResource::collection($users));
    }
}
