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
        Schema::create('student_fee_component_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_component_id')->constrained()->cascadeOnDelete();
            $table->string('component_name');
            $table->decimal('component_amount', 10, 2);
            $table->text('component_description')->nullable();
            $table->timestamps();
            $table->index(['student_fee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_component_snapshots');
    }
};