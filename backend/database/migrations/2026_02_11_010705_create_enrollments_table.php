<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignId('student_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add unique constraint and indexes
            $table->unique(['subject_id', 'student_id']);
            $table->index('student_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
