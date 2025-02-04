<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\Admin\CategoryResource as AdminCategoryResource;
use App\Http\Resources\DataSelectResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function fetchForSelect()
    {
        $categories = Category::query()->get();
        return response()->json([
            'categories' => DataSelectResource::collection($categories)
        ]);
    }

    public function fetchAll()
    {
        $categories = Category::query()->withoutGlobalScope('active')->get();
        return response()->json([
            'data' => AdminCategoryResource::collection($categories)
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $validated = $request->validated();

        $category = Category::create($validated);

        return response()->json([
            'category' => AdminCategoryResource::make($category)
        ]);
    }

    public function update(Category $category, CategoryRequest $request)
    {
        $validated = $request->validated();
        $category->update($validated);
        $category->save();

        return response()->json([
            'category' => AdminCategoryResource::make($category)
        ]);
    }

    public function destroy(Category $category)
    {
        $category->is_archived = true;
        $category->save();

        return response()->noContent();
    }

    public function restore(int $id)
    {
        $category = Category::withoutGlobalScope('active')->findOrFail($id);
        $category->is_archived = false;
        $category->save();

        return response()->noContent();
    }
}