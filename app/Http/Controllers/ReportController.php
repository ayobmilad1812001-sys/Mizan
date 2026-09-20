<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::with(['user', 'customer', 'warehouse'])
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->latest('created_at')
            ->get();

        $summary = [
            'count' => $sales->count(),
            'subtotal' => $sales->reduce(fn ($c, $s) => bcadd($c, $s->subtotal, 3), '0'),
            'discount' => $sales->reduce(fn ($c, $s) => bcadd($c, $s->discount_amount, 3), '0'),
            'tax' => $sales->reduce(fn ($c, $s) => bcadd($c, $s->tax_amount, 3), '0'),
            'total' => $sales->reduce(fn ($c, $s) => bcadd($c, $s->total, 3), '0'),
        ];

        $byMethod = $sales->groupBy(fn ($s) => $s->payment_method->value)
            ->map(fn ($group) => $group->reduce(fn ($c, $s) => bcadd($c, $s->total, 3), '0'));

        return view('reports.sales', [
            'sales' => $sales,
            'summary' => $summary,
            'byMethod' => $byMethod,
            'from' => $from,
            'to' => $to,
            'users' => User::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function purchases(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $purchases = Purchase::with(['supplier', 'creator'])
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('created_at')
            ->get();

        $summary = [
            'count' => $purchases->count(),
            'total' => $purchases->reduce(fn ($c, $p) => bcadd($c, $p->total, 3), '0'),
            'received' => $purchases->where('status.value', 'received')->count(),
        ];

        return view('reports.purchases', compact('purchases', 'summary', 'from', 'to'));
    }

    public function inventory(Request $request): View
    {
        $stocks = WarehouseStock::with(['product', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->get()
            ->sortBy(fn ($s) => $s->product->name)
            ->values();

        if ($request->filled('level')) {
            $stocks = $stocks->filter(fn ($s) => $s->level()->value === $request->string('level')->value())->values();
        }

        $summary = [
            'lines' => $stocks->count(),
            'units' => $stocks->reduce(fn ($c, $s) => bcadd($c, $s->quantity, 3), '0'),
            // Valued at the base unit's purchase price: what the stock on hand cost.
            'value' => $stocks->reduce(function ($carry, $s) {
                $cost = $s->product->baseUnit?->purchase_price ?? '0';

                return bcadd($carry, bcmul($s->quantity, $cost, 3), 3);
            }, '0'),
        ];

        return view('reports.inventory', [
            'stocks' => $stocks,
            'summary' => $summary,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function profit(Request $request): View
    {
        [$from, $to] = $this->range($request);

        // Profit is computed from the cost frozen on each sale line, so later price
        // changes never rewrite the profit of invoices already issued.
        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.base_quantity) as base_quantity'),
                DB::raw('SUM(sale_items.line_total) as revenue'),
                DB::raw('SUM(sale_items.base_quantity * sale_items.unit_cost) as cost'),
                DB::raw('SUM(sale_items.returned_quantity) as returned_quantity'),
            ])
            ->orderByDesc(DB::raw('SUM(sale_items.line_total) - SUM(sale_items.base_quantity * sale_items.unit_cost)'))
            ->get()
            ->map(function ($row) {
                $row->profit = bcsub((string) $row->revenue, (string) $row->cost, 3);
                $row->margin = bccomp((string) $row->revenue, '0', 3) > 0
                    ? bcmul(bcdiv($row->profit, (string) $row->revenue, 6), '100', 2)
                    : '0.00';

                return $row;
            });

        $summary = [
            'revenue' => $rows->reduce(fn ($c, $r) => bcadd($c, (string) $r->revenue, 3), '0'),
            'cost' => $rows->reduce(fn ($c, $r) => bcadd($c, (string) $r->cost, 3), '0'),
        ];
        $summary['profit'] = bcsub($summary['revenue'], $summary['cost'], 3);

        return view('reports.profit', compact('rows', 'summary', 'from', 'to'));
    }

    public function movements(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $movements = StockMovement::with(['product', 'warehouse', 'user'])
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('movement_type'), fn ($q) => $q->where('movement_type', $request->string('movement_type')))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('reports.movements', [
            'movements' => $movements,
            'from' => $from,
            'to' => $to,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        return [$from->startOfDay(), $to->endOfDay()];
    }
}
