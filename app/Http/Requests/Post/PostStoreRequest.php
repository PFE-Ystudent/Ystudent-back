<?php

namespace App\Http\Requests\Post;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class PostStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'categories' => ['required' , 'array'],
            'categories.*' => ['exists:'.(new Category())->getTable().',id'],
            'integrations' => ['array'],
            'integrations.*.type' => ['required', 'in:survey,annonce'],
            'integrations.*.data.question' => ['required', 'string'],
            'integrations.*.data.options' => ['required', 'array', 'min:2', 'max:10'],
            'integrations.*.data.options.*' => ['required', 'string']
        ];
    }
}
