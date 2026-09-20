<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(private readonly StockAdjustmentService $adjustments) {}

    public function index(): View
    {
        $adjustments = StockAdjustment::with(['warehouse', 'user'])
            ->withCount('items')
            ->latest('created_at')
            ->paginate(20);

        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('status', 'active')->orderBy('name')->get();

        // Balances for every warehouse+product pair, so the form can show the current
        // quantity beside the counted one without a round trip. Fine at this catalogue
        // size; see the PRD note on moving this to an on-demand lookup as it grows.
        $balances = WarehouseStock::query()
            ->get(['warehouse_id', 'product_id', 'quantity'])
            ->mapWithKeys(fn ($s) => ["{$s->warehouse_id}:{$s->product_id}" => $s->quantity]);

        return view('stock-adjustments.create', compact('warehouses', 'products', 'balances'));
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $adjustment = $this->adjustments->create(
            $request->safe()->only(['warehouse_id', 'reason', 'idempotency_key']),
            $request->validated('items'),
            auth()->user(),
        );

        return redirect()->route('stock-adjustments.show', $adjustment)
            ->with('status', "سُجِّلت التسوية \"{$adjustment->reference_number}\".");
    }

    public function show(StockAdjustment $adjustment): View
    {
        $adjustment->load(['warehouse', 'user', 'items.product']);

        return view('stock-adjustments.show', compact('adjustment'));
    }
}
