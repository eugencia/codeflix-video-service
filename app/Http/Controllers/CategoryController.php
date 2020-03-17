<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    protected function model()
    {
        return Category::class;
    }

    protected function request()
    {
        return CategoryRequest::class;
    }

    protected function resource()
    {
        return CategoryResource::class;
    }
}
