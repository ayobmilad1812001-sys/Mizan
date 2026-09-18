<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SalesSession;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->hasPermission($ability) ?: null);

        Model::shouldBeStrict(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
            'setting' => Setting::class,
            'category' => Category::class,
            'brand' => Brand::class,
            'warehouse' => Warehouse::class,
            'customer' => Customer::class,
            'supplier' => Supplier::class,
            'product' => Product::class,
            'product_unit' => ProductUnit::class,
            'purchase' => Purchase::class,
            'purchase_receipt' => PurchaseReceipt::class,
            'stock_adjustment' => StockAdjustment::class,
            'stock_transfer' => StockTransfer::class,
            'sales_session' => SalesSession::class,
            'sale' => Sale::class,
            'return' => SaleReturn::class,
            'refund' => Refund::class,
        ]);
    }
}
