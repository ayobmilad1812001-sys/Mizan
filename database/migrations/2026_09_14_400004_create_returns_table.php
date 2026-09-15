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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->unsignedBigInteger('sale_id');
            $table->enum('sale_payment_method', ['cash', 'card', 'transfer'])->default('cash');
            $table->unsignedBigInteger('sales_session_id');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reason');
            $table->decimal('total_amount', 15, 3);
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign(['sale_id', 'sale_payment_method'])
                ->references(['id', 'payment_method'])->on('sales')
                ->restrictOnDelete();
            $table->foreign(['sales_session_id', 'user_id'])
                ->references(['id', 'user_id'])->on('sales_sessions')
                ->restrictOnDelete();
            $table->unique(['id', 'sale_id']);
            $table->unique(['id', 'total_amount']);
        });

        DB::statement("ALTER TABLE returns
            ADD CONSTRAINT returns_cash_only_check CHECK (sale_payment_method = 'cash'),
            ADD CONSTRAINT returns_reason_check CHECK (CHAR_LENGTH(TRIM(reason)) > 0),
            ADD CONSTRAINT returns_amount_check CHECK (total_amount > 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
