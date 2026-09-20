<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockTransferRequest;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private readonly StockTransferService $transfers) {}

    public function index(): View
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->withCount('items')
            ->latest('created_at')
            ->paginate(20);

        return view('stock-transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        $products = Product::with(['units' => fn ($q) => $q->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();

        // Source-warehouse balances, so the form can warn before submitting a quantity
        // the source does not have. The authoritative check happens under lock on save.
        $balances = WarehouseStock::query()
            ->get(['warehouse_id', 'product_id', 'quantity'])
            ->mapWithKeys(fn ($s) => ["{$s->warehouse_id}:{$s->product_id}" => $s->quantity]);

        return view('stock-transfers.create', compact('warehouses', 'products', 'balances'));
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $transfer = $this->transfers->create(
            $request->safe()->only(['from_warehouse_id', 'to_warehouse_id', 'notes', 'idempotency_key']),
            $request->validated('items'),
            auth()->user(),
        );

        return redirect()->route('stock-transfers.show', $transfer)
            ->with('status', "سُجِّل التحويل \"{$transfer->reference_number}\".");
    }

    public function show(StockTransfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'user', 'items.product', 'items.unit']);

        return view('stock-transfers.show', compact('transfer'));
    }
}
