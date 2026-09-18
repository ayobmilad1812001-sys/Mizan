<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'brand', 'units'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('sku', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create(): View
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($request->safe()->only([
                'name', 'sku', 'category_id', 'brand_id', 'description', 'reorder_level',
            ]));

            foreach ($request->validated('units') as $unit) {
                (new ProductUnit)->forceFill([
                    'product_id' => $product->id,
                    'name' => $unit['name'],
                    'factor' => $unit['factor'],
                    'is_base' => (bool) ($unit['is_base'] ?? false),
                    'barcode' => $unit['barcode'] ?? null,
                    'purchase_price' => $unit['purchase_price'],
                    'selling_price' => $unit['selling_price'],
                ])->save();
            }

            return $product;
        });

        return redirect()->route('products.index')->with('status', "أُضيف المنتج \"{$product->name}\" بنجاح.");
    }

    public function edit(Product $product): View
    {
        $product->load('units');
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return back()->with('status', 'حُفظ المنتج.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->forceFill([
            'status' => $product->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة المنتج.');
    }
}
