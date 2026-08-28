<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->integer('ca_max')->default(30)->after('periods_per_week');
            $table->integer('exam_max')->default(70)->after('ca_max');
        });
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn(['ca_max', 'exam_max']);
        });
    }
};
