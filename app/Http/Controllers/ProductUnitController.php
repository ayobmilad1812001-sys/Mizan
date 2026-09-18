<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductUnitRequest;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;

class ProductUnitController extends Controller
{
    public function store(StoreProductUnitRequest $request, Product $product): RedirectResponse
    {
        (new ProductUnit)->forceFill([
            'product_id' => $product->id,
            'is_base' => false,
            ...$request->validated(),
        ])->save();

        return back()->with('status', 'أُضيفت الوحدة.');
    }

    public function toggleStatus(Product $product, ProductUnit $unit): RedirectResponse
    {
        abort_unless($unit->product_id === $product->id, 404);

        $unit->forceFill([
            'status' => $unit->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة الوحدة.');
    }
}
