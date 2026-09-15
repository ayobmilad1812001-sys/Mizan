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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->unsignedBigInteger('sales_session_id');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'transfer'])->index();
            $table->decimal('subtotal', 15, 3);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 3)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 3)->default(0);
            $table->decimal('total', 15, 3);
            $table->string('notes')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign(['sales_session_id', 'user_id'])
                ->references(['id', 'user_id'])->on('sales_sessions')
                ->restrictOnDelete();
            $table->unique(['id', 'payment_method']);
        });

        DB::statement('ALTER TABLE sales
            ADD CONSTRAINT sales_amounts_check CHECK (subtotal >= 0 AND discount_amount >= 0 AND tax_amount >= 0 AND total >= 0),
            ADD CONSTRAINT sales_rates_check CHECK (discount_percent BETWEEN 0 AND 100 AND tax_rate BETWEEN 0 AND 100),
            ADD CONSTRAINT sales_discount_check CHECK (discount_amount = ROUND(subtotal * discount_percent / 100, 3)),
            ADD CONSTRAINT sales_tax_check CHECK (tax_amount = ROUND((subtotal - discount_amount) * tax_rate / 100, 3)),
            ADD CONSTRAINT sales_total_check CHECK (total = subtotal - discount_amount + tax_amount)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
