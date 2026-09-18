<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivePurchaseRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchases) {}

    public function index(): View
    {
        $purchases = Purchase::with(['supplier', 'creator'])->latest('created_at')->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::with(['units' => fn ($q) => $q->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $purchase = $this->purchases->create(
            $request->safe()->only(['supplier_id', 'notes']),
            $request->validated('items'),
            auth()->user(),
        );

        return redirect()->route('purchases.show', $purchase)->with('status', "أُنشئ أمر الشراء \"{$purchase->reference_number}\".");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'creator', 'confirmer', 'canceller', 'items.product', 'items.unit', 'receipt.warehouse', 'receipt.receiver']);

        return view('purchases.show', compact('purchase'));
    }

    public function confirm(Purchase $purchase): RedirectResponse
    {
        $this->purchases->confirm($purchase, auth()->user());

        return back()->with('status', 'أُكِّد أمر الشراء.');
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->purchases->cancel($purchase, auth()->user());

        return back()->with('status', 'أُلغي أمر الشراء.');
    }

    public function receiveIndex(): View
    {
        $purchases = Purchase::with('supplier')->where('status', 'confirmed')->latest('confirmed_at')->paginate(20);

        return view('purchases.receive-index', compact('purchases'));
    }

    public function receiveForm(Purchase $purchase): View
    {
        abort_unless($purchase->canBeReceived(), 404);
        $purchase->load(['supplier', 'items.product', 'items.unit']);
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        return view('purchases.receive-form', compact('purchase', 'warehouses'));
    }

    public function receive(ReceivePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $warehouse = Warehouse::findOrFail($request->validated('warehouse_id'));

        // Redirect to the order page explicitly rather than relying on the
        // global RuntimeException handler's back(): the natural "previous"
        // page here is the receive form itself, which 404s once the order is
        // already received — that would swallow the error message entirely.
        try {
            $this->purchases->receive($purchase, $warehouse, auth()->user());
        } catch (RuntimeException $e) {
            return redirect()->route('purchases.show', $purchase)->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'استُلمت البضاعة وأُضيفت إلى المخزون.');
    }
}
