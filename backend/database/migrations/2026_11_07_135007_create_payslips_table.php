<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id')->constrained()->restrictOnDelete();
            $table->string('payslip_number')->unique();
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users')->nullOnDelete();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['payroll_record_id']);
            $table->index(['payslip_number']);
            $table->index(['generated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
