<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function fetchAll()
    {
        $categories = Category::query()->where('is_archived', false)->get();
        return response()->json(['categories' => $categories]);
    }

}