<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50);
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index('is_read');
            $table->index('created_at');
            $table->index(['user_id', 'is_read']);
        });
        
        DB::statement("ALTER TABLE user_notifications ADD CONSTRAINT chk_user_notifications_type CHECK (type IN ('assignment_created', 'deadline_reminder', 'graded', 'resubmit_required', 'system'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE user_notifications DROP CONSTRAINT IF EXISTS chk_user_notifications_type");
        Schema::dropIfExists('user_notifications');
    }
};
