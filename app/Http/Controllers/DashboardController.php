<?php

namespace App\Http\Controllers;

use App\Enums\StockLevel;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SalesSession;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $today = now()->startOfDay();

        // Each tile is permission-gated: a cashier must not learn the shop's turnover
        // just by opening the home page.
        $cards = [];

        if ($user->hasPermission('sales.view_all')) {
            $todaySales = Sale::whereDate('created_at', $today);
            $cards['sales_today'] = [
                'label' => 'مبيعات اليوم',
                'value' => (string) ($todaySales->clone()->sum('total') ?: '0.000'),
                'sub' => $todaySales->clone()->count().' فاتورة',
            ];
        } elseif ($user->hasPermission('sales.view_own')) {
            $mine = Sale::where('user_id', $user->id)->whereDate('created_at', $today);
            $cards['sales_today'] = [
                'label' => 'مبيعاتي اليوم',
                'value' => (string) ($mine->clone()->sum('total') ?: '0.000'),
                'sub' => $mine->clone()->count().' فاتورة',
            ];
        }

        if ($user->hasPermission('inventory.view')) {
            $stocks = WarehouseStock::with('product')->get();
            $low = $stocks->filter(fn ($s) => $s->level() !== StockLevel::Available)->count();

            $cards['stock_alerts'] = [
                'label' => 'أصناف تحتاج انتباهاً',
                'value' => (string) $low,
                'sub' => 'نفدت أو قاربت على النفاد',
                'tone' => $low > 0 ? 'warn' : 'ok',
            ];
        }

        if ($user->hasPermission('purchases.view')) {
            $awaiting = Purchase::where('status', 'confirmed')->count();
            $cards['purchases_awaiting'] = [
                'label' => 'أوامر بانتظار الاستلام',
                'value' => (string) $awaiting,
                'sub' => 'مؤكَّدة ولم تُستلم بعد',
            ];
        }

        if ($user->hasPermission('sessions.view_all')) {
            $open = SalesSession::where('status', 'open')->count();
            $cards['open_sessions'] = [
                'label' => 'جلسات مفتوحة الآن',
                'value' => (string) $open,
                'sub' => 'موظفون على نقاط البيع',
            ];
        }

        $lowStock = $user->hasPermission('inventory.view')
            ? WarehouseStock::with(['product', 'warehouse'])->get()
                ->filter(fn ($s) => $s->level() !== StockLevel::Available)
                ->sortBy('quantity')
                ->take(8)
                ->values()
            : collect();

        $recentMovements = $user->hasPermission('stock_movements.view')
            ? StockMovement::with(['product', 'warehouse', 'user'])->latest('created_at')->take(8)->get()
            : collect();

        $recentSales = $user->hasPermission('sales.view_all')
            ? Sale::with(['customer', 'user'])->latest('created_at')->take(8)->get()
            : ($user->hasPermission('sales.view_own')
                ? Sale::with(['customer', 'user'])->where('user_id', $user->id)->latest('created_at')->take(8)->get()
                : collect());

        $session = $user->hasPermission('sessions.start')
            ? SalesSession::where('user_id', $user->id)->where('status', 'open')->first()
            : null;

        return view('dashboard', compact('cards', 'lowStock', 'recentMovements', 'recentSales', 'session'));
    }
}
