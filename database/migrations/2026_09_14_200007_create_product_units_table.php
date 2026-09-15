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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('name', 30);
            $table->decimal('factor', 12, 4);
            $table->boolean('is_base')->default(false);
            $table->string('barcode', 50)->nullable()->unique();
            $table->decimal('purchase_price', 15, 3)->default(0);
            $table->decimal('selling_price', 15, 3)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unsignedBigInteger('base_key')->storedAs('IF(is_base, product_id, NULL)')->unique();
            $table->unique(['product_id', 'name']);
        });

        DB::statement('ALTER TABLE product_units
            ADD CONSTRAINT product_units_factor_check CHECK (factor > 0),
            ADD CONSTRAINT product_units_base_factor_check CHECK (is_base = 0 OR factor = 1),
            ADD CONSTRAINT product_units_prices_check CHECK (purchase_price >= 0 AND selling_price >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
