<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('last_name');
            $table->date('admission_date')->nullable()->after('date_of_birth');
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete()->after('class_id');
            $table->string('student_type')->nullable()->after('admission_date');
            $table->string('previous_school')->nullable()->after('student_type');
            $table->text('previous_school_address')->nullable()->after('previous_school');
            $table->string('previous_class')->nullable()->after('previous_school_address');
            $table->string('previous_year_attended')->nullable()->after('previous_class');
            $table->string('nationality')->nullable()->after('state_of_origin');
            $table->string('lga')->nullable()->after('nationality');
            $table->string('religion')->nullable()->after('lga');
            $table->text('home_address')->nullable()->after('gender');
            $table->string('city')->nullable()->after('home_address');
            $table->string('state')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn([
                'middle_name',
                'admission_date',
                'academic_session_id',
                'student_type',
                'previous_school',
                'previous_school_address',
                'previous_class',
                'previous_year_attended',
                'nationality',
                'lga',
                'religion',
                'home_address',
                'city',
                'state',
            ]);
        });
    }
};
