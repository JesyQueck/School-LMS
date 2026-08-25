<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('class_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
