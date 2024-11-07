<?php

namespace App\Http\Requests\Post;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class PostIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'author' => ['sometimes', 'nullable', 'string'],
            'search' => ['sometimes', 'nullable', 'string'],
            'categories' => ['sometimes', 'nullable', 'array'],
            'categories.*' => ['exists:' . (new Category())->getTable() . ',id']
        ];
    }
}
