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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('product_unit_id');
            $table->decimal('unit_factor', 12, 4);
            $table->decimal('quantity', 12, 3);
            $table->decimal('base_quantity', 12, 3);
            $table->decimal('unit_price', 15, 3);
            $table->decimal('unit_cost', 15, 3);
            $table->decimal('discount_share', 15, 3)->default(0);
            $table->decimal('line_total', 15, 3);
            $table->decimal('net_unit_price', 15, 3);
            $table->decimal('returned_quantity', 12, 3)->default(0);

            $table->foreign(['product_unit_id', 'product_id'])
                ->references(['id', 'product_id'])->on('product_units')
                ->restrictOnDelete();
            $table->unique(['sale_id', 'product_unit_id']);
            $table->unique(['id', 'sale_id']);
        });

        DB::statement('ALTER TABLE sale_items
            ADD CONSTRAINT sale_items_quantity_check CHECK (quantity > 0 AND unit_factor > 0),
            ADD CONSTRAINT sale_items_base_quantity_check CHECK (base_quantity = TRUNCATE(quantity * unit_factor, 3)),
            ADD CONSTRAINT sale_items_amounts_check CHECK (unit_price >= 0 AND unit_cost >= 0 AND discount_share >= 0 AND line_total >= 0),
            ADD CONSTRAINT sale_items_line_total_check CHECK (line_total = ROUND(quantity * unit_price, 3) - discount_share),
            ADD CONSTRAINT sale_items_net_price_check CHECK (net_unit_price = ROUND(line_total / quantity, 3)),
            ADD CONSTRAINT sale_items_returned_check CHECK (returned_quantity >= 0 AND returned_quantity <= quantity)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
