<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserDetailsResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show (User $user) {
        $user->loadCount(['posts', 'postReplies']);

        return response()->json(UserDetailsResource::make($user));
    }
}
