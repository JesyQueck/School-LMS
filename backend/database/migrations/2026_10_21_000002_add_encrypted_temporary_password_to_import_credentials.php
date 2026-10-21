<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_credentials', function (Blueprint $table) {
            $table->longText('temporary_password')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('import_credentials', function (Blueprint $table) {
            $table->dropColumn('temporary_password');
        });
    }
};
