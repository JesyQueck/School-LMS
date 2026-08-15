<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicate = DB::table('fee_types')
            ->select('name', 'term_id', 'class_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('class_id')
            ->groupBy('name', 'term_id', 'class_id')
            ->having('cnt', '>', 1)
            ->exists();

        if ($hasDuplicate) {
            return;
        }

        Schema::table('fee_types', function (Blueprint $table) {
            $table->unique(['name', 'term_id', 'class_id'], 'fee_types_name_term_class_unique');
        });
    }

    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            $table->dropUnique('fee_types_name_term_class_unique');
        });
    }
};
