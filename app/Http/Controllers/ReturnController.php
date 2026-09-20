<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ReturnController extends Controller
{
    public function __construct(private readonly ReturnService $returns) {}

    public function index(): View
    {
        $user = auth()->user();

        $returns = SaleReturn::with(['sale.customer', 'user', 'refund'])
            // Without returns.view_all an employee sees only their own returns.
            ->unless($user->hasPermission('returns.view_all'), fn ($q) => $q->where('user_id', $user->id))
            ->latest('created_at')
            ->paginate(20);

        return view('returns.index', compact('returns'));
    }

    public function create(Sale $sale): View
    {
        abort_unless($sale->isReturnable(), 404);

        $sale->load(['items.product', 'items.unit', 'customer', 'warehouse']);

        // Nothing left to give back: every line has already been fully returned.
        abort_if(
            $sale->items->every(fn ($item) => bccomp($item->returnableQuantity(), '0', 3) <= 0),
            404,
        );

        return view('returns.create', compact('sale'));
    }

    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $saleId = (int) $request->validated('sale_id');

        // Redirect explicitly rather than relying on the global RuntimeException
        // handler's back(): the return form 404s once the invoice is fully returned,
        // which would swallow the message.
        try {
            $return = $this->returns->create(
                $request->safe()->only(['sale_id', 'reason', 'idempotency_key']),
                $request->validated('items'),
                auth()->user(),
            );
        } catch (RuntimeException $e) {
            return redirect()->route('sales.show', $saleId)->with('error', $e->getMessage());
        }

        return redirect()->route('returns.show', $return)
            ->with('status', "سُجِّل المرتجع \"{$return->reference_number}\" ورُدَّ المبلغ نقداً.");
    }

    public function show(SaleReturn $return): View
    {
        $user = auth()->user();

        abort_unless(
            $return->user_id === $user->id || $user->hasPermission('returns.view_all'),
            403,
        );

        $return->load(['sale.customer', 'sale.warehouse', 'user', 'refund', 'items.saleItem.product', 'items.saleItem.unit']);

        return view('returns.show', compact('return'));
    }
}
