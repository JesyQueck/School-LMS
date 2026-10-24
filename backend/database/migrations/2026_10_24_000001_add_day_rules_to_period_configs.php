<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_configs', function (Blueprint $table) {
            $table->json('day_rules')->nullable()->after('end_day');
        });
    }

    public function down(): void
    {
        Schema::table('period_configs', function (Blueprint $table) {
            $table->dropColumn('day_rules');
        });
    }
};
