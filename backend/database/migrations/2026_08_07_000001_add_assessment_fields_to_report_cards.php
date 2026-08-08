<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->text('affective_domain')->nullable()->after('class_teacher_remark');
            $table->text('psychomotor_assessment')->nullable()->after('affective_domain');
            $table->text('health_remarks')->nullable()->after('psychomotor_assessment');
            $table->text('promotion_decision')->nullable()->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropColumn(['affective_domain', 'psychomotor_assessment', 'health_remarks', 'promotion_decision']);
        });
    }
};