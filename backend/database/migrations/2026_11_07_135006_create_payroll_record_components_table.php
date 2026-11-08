<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_record_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('component_name');
            $table->string('component_type'); // allowance, deduction
            $table->decimal('amount', 12, 2);
            $table->string('calculation_basis'); // fixed, percentage
            $table->decimal('calculation_value', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['payroll_record_id']);
            $table->index(['salary_component_id']);
            $table->index(['component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_record_components');
    }
};
