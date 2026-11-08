<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add unique constraint on payslips.payroll_record_id (one payslip per payroll record)
        Schema::table('payslips', function (Blueprint $table) {
            $table->unique(['payroll_record_id'], 'unique_payslip_per_payroll_record');
        });

        // 2. Add composite unique constraint on salary_profile_components to prevent
        //    duplicate assignment of the same component to the same profile with the
        //    same effective start date. NULL effective_from values are treated as
        //    distinct by MySQL 8.4, preserving legitimate historical assignments.
        Schema::table('salary_profile_components', function (Blueprint $table) {
            $table->unique(
                ['salary_profile_id', 'salary_component_id', 'effective_from'],
                'unique_profile_component_effective_date'
            );
        });

        // 3. Replace destructive cascadeOnDelete with restrictive deletion on
        //    salary_profile_components.salary_profile_id
        Schema::table('salary_profile_components', function (Blueprint $table) {
            $table->dropForeign(['salary_profile_id']);
            $table->foreign('salary_profile_id')
                ->references('id')
                ->on('salary_profiles')
                ->onDelete('restrict');
        });

        // 4. Change payroll_record_components.payroll_record_id from cascadeOnDelete
        //    to restrictOnDelete so deleting a payroll record cannot destroy
        //    historical component snapshots.
        Schema::table('payroll_record_components', function (Blueprint $table) {
            $table->dropForeign(['payroll_record_id']);
            $table->foreign('payroll_record_id')
                ->references('id')
                ->on('payroll_records')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropUnique('unique_payslip_per_payroll_record');
        });

        Schema::table('salary_profile_components', function (Blueprint $table) {
            $table->dropUnique('unique_profile_component_effective_date');
            $table->dropForeign(['salary_profile_id']);
            $table->foreign('salary_profile_id')
                ->references('id')
                ->on('salary_profiles')
                ->onDelete('cascade');
        });

        Schema::table('payroll_record_components', function (Blueprint $table) {
            $table->dropForeign(['payroll_record_id']);
            $table->foreign('payroll_record_id')
                ->references('id')
                ->on('payroll_records')
                ->onDelete('cascade');
        });
    }
};
