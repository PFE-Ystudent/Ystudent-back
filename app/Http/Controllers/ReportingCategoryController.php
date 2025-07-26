<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReportingCategoryRequest;
use App\Http\Resources\Admin\ReportingCategoryResource as AdminReportingCategoryResource;
use App\Http\Resources\DataSelectResource;
use App\Models\ReportingCategory;

class ReportingCategoryController extends Controller
{
    public function fetchForSelect(string $type)
    {
        $categories = ReportingCategory::query()->where('for_bug', $type === 'bug')->get();
        return response()->json([
            'categories' => DataSelectResource::collection($categories)
        ]);
    }

    public function fetchAll()
    {
        $categories = ReportingCategory::query()->withoutGlobalScope('active')->get();
        return response()->json([
            'data' => AdminReportingCategoryResource::collection($categories)
        ]);
    }

    public function store(ReportingCategoryRequest $request)
    {
        $validated = $request->validated();

        $category = ReportingCategory::create($validated);

        return response()->json([
            'category' => AdminReportingCategoryResource::make($category)
        ]);
    }

    public function update(ReportingCategory $category, ReportingCategoryRequest $request)
    {
        $validated = $request->validated();
        $category->update($validated);
        $category->save();

        return response()->json([
            'category' => AdminReportingCategoryResource::make($category)
        ]);
    }

    public function destroy(ReportingCategory $category)
    {
        $category->is_archived = true;
        $category->save();

        return response()->noContent();
    }

    public function restore(int $id)
    {
        $category = ReportingCategory::withoutGlobalScope('active')->findOrFail($id);
        $category->is_archived = false;
        $category->save();

        return response()->noContent();
    }
}