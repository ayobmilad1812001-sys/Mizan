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
        Schema::create('sales_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->decimal('opening_float', 15, 3)->default(0);
            $table->decimal('expected_cash', 15, 3)->nullable();
            $table->decimal('actual_cash', 15, 3)->nullable();
            $table->decimal('difference', 15, 3)->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at')->useCurrent()->index();
            $table->timestamp('closed_at')->nullable();

            $table->unsignedBigInteger('active_key')->storedAs("IF(status = 'open', user_id, NULL)")->unique();
            $table->unique(['id', 'user_id']);
        });

        DB::statement("ALTER TABLE sales_sessions
            ADD CONSTRAINT sales_sessions_cash_check CHECK (opening_float >= 0 AND (actual_cash IS NULL OR actual_cash >= 0)),
            ADD CONSTRAINT sales_sessions_status_check CHECK (
                (status = 'open' AND closed_at IS NULL AND expected_cash IS NULL AND actual_cash IS NULL AND difference IS NULL)
                OR (status = 'closed' AND closed_at IS NOT NULL AND closed_at >= opened_at
                    AND expected_cash IS NOT NULL AND actual_cash IS NOT NULL
                    AND difference = actual_cash - expected_cash)
            )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_sessions');
    }
};
