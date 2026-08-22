<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->integer('periods_per_day')->default(8);
            $table->string('start_day')->default('Monday');
            $table->string('end_day')->default('Friday');
            $table->timestamps();
            $table->unique(['academic_session_id', 'term_id']);
        });

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_config_id')->constrained()->cascadeOnDelete();
            $table->integer('period_number');
            $table->string('name')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['period_config_id', 'period_number']);
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->foreignId('period_config_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('class_subjects', function (Blueprint $table) {
            $table->integer('periods_per_week')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn('periods_per_week');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropForeign(['period_config_id']);
            $table->dropColumn(['is_locked', 'is_manual', 'period_config_id']);
        });

        Schema::dropIfExists('periods');
        Schema::dropIfExists('period_configs');
    }
};
