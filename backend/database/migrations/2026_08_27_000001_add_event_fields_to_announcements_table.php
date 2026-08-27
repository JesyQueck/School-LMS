<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_event')->default(false)->after('show_on_website');
            $table->date('event_date')->nullable()->after('is_event');
            $table->time('event_time')->nullable()->after('event_date');
            $table->string('event_location')->nullable()->after('event_time');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['is_event', 'event_date', 'event_time', 'event_location']);
        });
    }
};
