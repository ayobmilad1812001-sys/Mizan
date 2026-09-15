<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Lets line tables reference (product_unit_id, product_id) together,
     * so a unit can never be recorded against a product it does not belong to.
     */
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->unique(['id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUnique(['id', 'product_id']);
        });
    }
};
