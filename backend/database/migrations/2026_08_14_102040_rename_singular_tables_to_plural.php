<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance') && ! Schema::hasTable('attendances')) {
            Schema::rename('attendance', 'attendances');
        }

        if (Schema::hasTable('timetable') && ! Schema::hasTable('timetables')) {
            Schema::rename('timetable', 'timetables');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances') && ! Schema::hasTable('attendance')) {
            Schema::rename('attendances', 'attendance');
        }

        if (Schema::hasTable('timetables') && ! Schema::hasTable('timetable')) {
            Schema::rename('timetables', 'timetable');
        }
    }
};
