<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(20);

        return view('categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return back()->with('status', 'أُضيف التصنيف بنجاح.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return back()->with('status', 'حُفظ التصنيف.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->forceFill([
            'status' => $category->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة التصنيف.');
    }
}
