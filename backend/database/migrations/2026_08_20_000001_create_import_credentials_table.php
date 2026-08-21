<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('role')->comment('student, parent, teacher');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('related_to')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('import_credentials', function (Blueprint $table) {
            $table->index(['created_by', 'role']);
            $table->index(['created_by', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_credentials');
    }
};
