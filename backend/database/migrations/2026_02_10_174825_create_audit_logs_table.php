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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 20);
            $table->string('model', 100);
            $table->unsignedBigInteger('model_id')->default(0);
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index('model');
            $table->index('created_at');
            $table->index(['model', 'model_id']);
        });

        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_logs_action CHECK (action IN ('create', 'update', 'delete', 'login', 'logout'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE audit_logs DROP CONSTRAINT IF EXISTS chk_audit_logs_action");
        Schema::dropIfExists('audit_logs');
    }
};
