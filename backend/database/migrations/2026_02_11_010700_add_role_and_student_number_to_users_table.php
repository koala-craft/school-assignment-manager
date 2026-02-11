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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student')->after('email');
            $table->string('student_number', 50)->nullable()->unique()->after('role');
            $table->boolean('is_active')->default(true)->after('student_number');
            $table->boolean('is_first_login')->default(true)->after('is_active');
            $table->softDeletes()->after('updated_at');
            
            // Add index
            $table->index('role');
            $table->index('is_active');
        });
        
        // Add CHECK constraint for role
        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('admin', 'teacher', 'student_admin', 'student'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop CHECK constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_role");
            
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'student_number', 'is_active', 'is_first_login', 'deleted_at']);
        });
    }
};
