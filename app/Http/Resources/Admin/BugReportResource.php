<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\AuthorResource;
use App\Http\Resources\DataSelectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugReportResource extends JsonResource
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
            'author' => AuthorResource::make($this->user),
            'category' => DataSelectResource::make($this->reportingCategory),
            'description' => $this->description,
            'note' => $this->note,
            'important' => (bool)$this->important,
            'isProcessed' => (bool)$this->is_processed,
            'isDone' => (bool)$this->is_done,
            'isArchived' => (bool)$this->is_archived,
            'createdAt' => $this->created_at,
        ];
    }
}
