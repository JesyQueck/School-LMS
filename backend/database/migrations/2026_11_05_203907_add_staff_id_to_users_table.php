<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'staff_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('staff_id')->nullable();
            });
        }

        if (!Schema::hasIndex('users', 'users_staff_id_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('staff_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_staff_id_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_staff_id_unique');
            });
        }

        if (Schema::hasColumn('users', 'staff_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('staff_id');
            });
        }
    }
};