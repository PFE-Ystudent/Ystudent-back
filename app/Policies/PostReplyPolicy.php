<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostReplyPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PostReply $postReply): Response
    {
        return !$postReply->is_archived ? Response::allow()
        : Response::denyWithStatus(404);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PostReply $postReply): bool
    {
        return $user->id === $postReply->user_id && !$postReply->is_archived;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostReply $postReply): bool
    {
        return $user->id === $postReply->user_id && !$postReply->is_archived;
    }
}
