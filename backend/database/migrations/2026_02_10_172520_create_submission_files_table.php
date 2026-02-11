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
        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->string('filename', 255);
            $table->string('original_filename', 255);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('storage_path', 500);
            $table->integer('version')->default(1);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->softDeletes();
            
            // Add indexes
            $table->index('submission_id');
            $table->index('version');
            $table->index('deleted_at');
        });
        
        // Add CHECK constraints
        DB::statement("ALTER TABLE submission_files ADD CONSTRAINT chk_submission_files_size CHECK (file_size > 0)");
        DB::statement("ALTER TABLE submission_files ADD CONSTRAINT chk_submission_files_version CHECK (version > 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE submission_files DROP CONSTRAINT IF EXISTS chk_submission_files_size");
        DB::statement("ALTER TABLE submission_files DROP CONSTRAINT IF EXISTS chk_submission_files_version");
        Schema::dropIfExists('submission_files');
    }
};
