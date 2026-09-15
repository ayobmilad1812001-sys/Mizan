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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id')->unique();
            $table->decimal('amount', 15, 3);
            $table->enum('method', ['cash'])->default('cash');
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign(['return_id', 'amount'])
                ->references(['id', 'total_amount'])->on('returns')
                ->restrictOnDelete();
        });

        DB::statement('ALTER TABLE refunds
            ADD CONSTRAINT refunds_amount_check CHECK (amount > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
