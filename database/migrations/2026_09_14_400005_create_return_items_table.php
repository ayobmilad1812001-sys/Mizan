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
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('sale_item_id');
            $table->decimal('quantity', 12, 3);
            $table->decimal('base_quantity', 12, 3);
            $table->decimal('unit_price', 15, 3);
            $table->decimal('line_total', 15, 3);

            $table->foreign(['return_id', 'sale_id'])
                ->references(['id', 'sale_id'])->on('returns')
                ->cascadeOnDelete();
            $table->foreign(['sale_item_id', 'sale_id'])
                ->references(['id', 'sale_id'])->on('sale_items')
                ->restrictOnDelete();
            $table->unique(['return_id', 'sale_item_id']);
        });

        DB::statement('ALTER TABLE return_items
            ADD CONSTRAINT return_items_quantity_check CHECK (quantity > 0 AND base_quantity > 0),
            ADD CONSTRAINT return_items_amounts_check CHECK (unit_price >= 0 AND line_total >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
