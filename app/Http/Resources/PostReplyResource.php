<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostReplyResource extends JsonResource
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
            'content' => $this->content,
            'author' => AuthorResource::make($this->author),
            'upCount' => $this->post_reply_ups_count ?? 0,
            'isUpVoted' => $this->isUpVoted,
            'files' => $this->files,
            'createdAt' => $this->created_at,
            'isEdited' => $this->isEdited,
        ];
    }
}
