<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'replyCount' => $this->whenNotNull($this->replies_count),
            'author' => AuthorResource::make($this->author),
            'categories' => CategoryResource::collection($this->categories),
            'surveys' => SurveyResource::collection($this->surveys),
            'isFavorited' => $this->is_favorited_by_user_exists,
            'files' => $this->files,
            'createdAt' => $this->created_at,
            'isEdited' => $this->isEdited,
        ];
    }
}
