<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\SaleService;
use App\Services\SalesSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $sales,
        private readonly SalesSessionService $sessions,
    ) {}

    public function pos(): View
    {
        $session = $this->sessions->currentFor(auth()->user());

        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::with(['units' => fn ($q) => $q->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();

        $balances = WarehouseStock::query()
            ->get(['warehouse_id', 'product_id', 'quantity'])
            ->mapWithKeys(fn ($s) => ["{$s->warehouse_id}:{$s->product_id}" => $s->quantity]);

        $taxRate = (string) (Setting::where('name', 'tax_rate')->value('value') ?? '0');

        return view('sales.pos', compact('session', 'warehouses', 'customers', 'products', 'balances', 'taxRate'));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        // Redirect back to the till explicitly rather than relying on the global
        // RuntimeException handler's back(): a failed sale must land the cashier on a
        // working screen with the basket intact, not on whatever page came before.
        try {
            $sale = $this->sales->create(
                $request->safe()->only(['warehouse_id', 'customer_id', 'payment_method', 'discount_percent', 'notes', 'idempotency_key']),
                $request->validated('items'),
                auth()->user(),
            );
        } catch (RuntimeException $e) {
            return redirect()->route('sales.pos')->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('status', "سُجِّلت الفاتورة \"{$sale->invoice_number}\".");
    }

    public function index(): View
    {
        $user = auth()->user();

        $sales = Sale::with(['customer', 'user', 'warehouse'])
            // Without sales.view_all an employee sees only their own invoices.
            ->unless($user->hasPermission('sales.view_all'), fn ($q) => $q->where('user_id', $user->id))
            ->latest('created_at')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $user = auth()->user();

        abort_unless(
            $sale->user_id === $user->id || $user->hasPermission('sales.view_all'),
            403,
        );

        $sale->load(['items.product', 'items.unit', 'customer', 'user', 'warehouse', 'session', 'returns.user']);

        return view('sales.show', compact('sale'));
    }
}
