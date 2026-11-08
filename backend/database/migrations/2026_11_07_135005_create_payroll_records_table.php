<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->restrictOnDelete();
            $table->decimal('basic_salary_snapshot', 12, 2);
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('total_allowances', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2);
            $table->string('status')->default('calculated')->comment('calculated, approved, paid, voided');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'payroll_period_id'], 'unique_employee_period_payroll');
            $table->index(['employee_id']);
            $table->index(['payroll_period_id']);
            $table->index(['status']);
            $table->index(['approved_by']);
            $table->index(['paid_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
