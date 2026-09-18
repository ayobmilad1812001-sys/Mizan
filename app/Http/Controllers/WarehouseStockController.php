<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseStockController extends Controller
{
    public function index(Request $request): View
    {
        $stocks = WarehouseStock::query()
            ->with(['product', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('product', fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('sku', 'like', '%'.$request->string('search').'%')))
            ->orderBy('product_id')
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        return view('warehouse-stocks.index', compact('stocks', 'warehouses'));
    }
}
