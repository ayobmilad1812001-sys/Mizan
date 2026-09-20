<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->string('q'));
        $user = auth()->user();

        $products = collect();
        $sales = collect();
        $purchases = collect();
        $customers = collect();

        if ($term !== '') {
            $like = '%'.$term.'%';

            if ($user->hasPermission('products.view')) {
                $products = Product::with('baseUnit')
                    ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('sku', 'like', $like))
                    ->orderBy('name')->take(10)->get();
            }

            if ($user->hasPermission('sales.view_all') || $user->hasPermission('sales.view_own')) {
                $sales = Sale::with(['customer', 'user'])
                    ->where('invoice_number', 'like', $like)
                    // An employee without view_all may only find their own invoices.
                    ->unless($user->hasPermission('sales.view_all'), fn ($q) => $q->where('user_id', $user->id))
                    ->latest('created_at')->take(10)->get();
            }

            if ($user->hasPermission('purchases.view')) {
                $purchases = Purchase::with('supplier')
                    ->where('reference_number', 'like', $like)
                    ->latest('created_at')->take(10)->get();
            }

            if ($user->hasPermission('customers.view')) {
                $customers = Customer::where(fn ($q) => $q->where('name', 'like', $like)->orWhere('phone', 'like', $like))
                    ->orderBy('name')->take(10)->get();
            }
        }

        return view('search', compact('term', 'products', 'sales', 'purchases', 'customers'));
    }
}
