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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->string('fee_source_type')->nullable()->after('fee_type_id'); // 'fee_type' or 'fee_package'
            $table->unsignedBigInteger('fee_package_id')->nullable()->after('fee_source_type');
            $table->foreign('fee_package_id')->references('id')->on('fee_packages')->nullOnDelete();
            $table->index(['fee_source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_package_id']);
            $table->dropColumn(['fee_source_type', 'fee_package_id']);
        });
    }
};