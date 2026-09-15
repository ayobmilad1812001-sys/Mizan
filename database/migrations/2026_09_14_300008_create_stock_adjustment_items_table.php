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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('previous_quantity', 12, 3);
            $table->decimal('new_quantity', 12, 3);
            $table->decimal('difference', 12, 3);

            $table->unique(['stock_adjustment_id', 'product_id']);
        });

        DB::statement('ALTER TABLE stock_adjustment_items
            ADD CONSTRAINT stock_adjustment_items_quantities_check CHECK (previous_quantity >= 0 AND new_quantity >= 0),
            ADD CONSTRAINT stock_adjustment_items_difference_check CHECK (difference = new_quantity - previous_quantity AND difference <> 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
