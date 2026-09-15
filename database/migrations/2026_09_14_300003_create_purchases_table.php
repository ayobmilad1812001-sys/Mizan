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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'confirmed', 'received', 'cancelled'])->default('draft')->index();
            $table->decimal('subtotal', 15, 3)->default(0);
            $table->decimal('total', 15, 3)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        DB::statement("ALTER TABLE purchases
            ADD CONSTRAINT purchases_amounts_check CHECK (subtotal >= 0 AND total >= 0),
            ADD CONSTRAINT purchases_status_check CHECK (
                (status = 'draft' AND confirmed_at IS NULL AND cancelled_at IS NULL)
                OR (status IN ('confirmed', 'received') AND confirmed_at IS NOT NULL AND confirmed_by IS NOT NULL AND cancelled_at IS NULL)
                OR (status = 'cancelled' AND cancelled_at IS NOT NULL AND cancelled_by IS NOT NULL)
            )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
