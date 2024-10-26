<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
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
            'question' => $this->question,
            'options' => $this->surveyOptions->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                    'isSelected' => $option->survey_option_replies_exists,
                    'replyCount' => $option->survey_option_replies_count,
                ];
            })
        ];
    }
}
