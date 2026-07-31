<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete()->after('is_published');
            $table->timestamp('published_at')->nullable()->after('published_by');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn(['published_by', 'published_at']);
        });
    }
};
