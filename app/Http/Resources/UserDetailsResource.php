<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'about' => $this->about,
            'createdAt' => $this->created_at,
            'postsCount' => $this->posts_count,
            'postRepliesCount' => $this->post_replies_count,
        ];
    }
}
