<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_reversals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reversed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->decimal('amount_reversed', 10, 2);
            $table->timestamp('reversed_at')->useCurrent();
            $table->timestamps();
            $table->unique('payment_id', 'unique_payment_reversal');
            $table->index(['reversed_by', 'reversed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reversals');
    }
};