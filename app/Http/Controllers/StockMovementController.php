<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['product', 'warehouse', 'user'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('product', fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('sku', 'like', '%'.$request->string('search').'%')))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        return view('stock-movements.index', compact('movements', 'warehouses'));
    }
}
