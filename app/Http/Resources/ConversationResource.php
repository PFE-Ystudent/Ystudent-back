<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'requester' => AuthorResource::make($this->requester),
            'user' => AuthorResource::make($this->user),
            'lastMessage' => $this->when($this->message_created_at, function () {
                return [
                    'content' => $this->message_content,
                    'createdAt' => $this->message_created_at
                ];
            }, null)
        ];
    }
}
