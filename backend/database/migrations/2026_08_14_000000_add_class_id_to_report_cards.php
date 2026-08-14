<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('report_cards', 'class_id')) {
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->after('term_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (Schema::hasColumn('report_cards', 'class_id')) {
                $table->dropForeign(['class_id']);
                $table->dropColumn('class_id');
            }
        });
    }
};
