<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEditResource;
use App\Http\Resources\UserAccountResource;
use App\Http\Resources\UserDetailsResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
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
}
