<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('movement_type', [
                'purchase_receipt', 'sale', 'return', 'adjustment', 'transfer_out', 'transfer_in',
            ])->index();
            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->morphs('reference');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['product_id', 'warehouse_id', 'created_at']);
        });

        DB::statement("ALTER TABLE stock_movements
            ADD CONSTRAINT stock_movements_balance_check CHECK (balance_after >= 0),
            ADD CONSTRAINT stock_movements_direction_check CHECK (
                (movement_type IN ('purchase_receipt', 'return', 'transfer_in') AND quantity > 0)
                OR (movement_type IN ('sale', 'transfer_out') AND quantity < 0)
                OR (movement_type = 'adjustment' AND quantity <> 0)
            )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
