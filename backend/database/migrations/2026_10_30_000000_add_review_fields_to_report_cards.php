<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('report_cards', 'review_date')) {
                $table->date('review_date')->nullable()->after('next_term_begins');
            }
            if (! Schema::hasColumn('report_cards', 'principal_signature')) {
                $table->string('principal_signature')->nullable()->after('review_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (Schema::hasColumn('report_cards', 'principal_signature')) {
                $table->dropColumn('principal_signature');
            }
            if (Schema::hasColumn('report_cards', 'review_date')) {
                $table->dropColumn('review_date');
            }
        });
    }
};
