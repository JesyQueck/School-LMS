<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('employment_type')->default('full-time')->comment('full-time, part-time, contract');
            $table->date('date_employed');
            $table->date('date_left')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('employment_status')->default('active')->comment('active, on_leave, terminated, retired');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('pension_id')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['employee_number']);
            $table->index(['employment_status']);
            $table->index(['department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
