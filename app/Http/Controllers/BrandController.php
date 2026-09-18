<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);

        return view('brands.index', compact('brands'));
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return back()->with('status', 'أُضيفت الماركة بنجاح.');
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return back()->with('status', 'حُفظت الماركة.');
    }

    public function toggleStatus(Brand $brand): RedirectResponse
    {
        $brand->forceFill([
            'status' => $brand->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة الماركة.');
    }
}
