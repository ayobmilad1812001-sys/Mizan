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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('product_unit_id');
            $table->decimal('unit_factor', 12, 4);
            $table->decimal('quantity', 12, 3);
            $table->decimal('base_quantity', 12, 3);
            $table->decimal('unit_cost', 15, 3);
            $table->decimal('line_total', 15, 3);

            $table->foreign(['product_unit_id', 'product_id'])
                ->references(['id', 'product_id'])->on('product_units')
                ->restrictOnDelete();
            $table->unique(['purchase_id', 'product_unit_id']);
        });

        DB::statement('ALTER TABLE purchase_items
            ADD CONSTRAINT purchase_items_quantity_check CHECK (quantity > 0 AND unit_factor > 0),
            ADD CONSTRAINT purchase_items_base_quantity_check CHECK (base_quantity = TRUNCATE(quantity * unit_factor, 3)),
            ADD CONSTRAINT purchase_items_amounts_check CHECK (unit_cost >= 0 AND line_total >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
