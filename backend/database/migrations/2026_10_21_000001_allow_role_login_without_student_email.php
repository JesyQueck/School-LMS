<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('import_credentials', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('login_identifier')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->whereNull('email')->exists()
            || DB::table('import_credentials')->whereNull('email')->exists()
            || DB::table('import_credentials')->whereNull('password')->exists()) {
            throw new RuntimeException('Cannot restore required credential fields while nullable values exist.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('import_credentials', function (Blueprint $table) {
            $table->dropColumn('login_identifier');
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
