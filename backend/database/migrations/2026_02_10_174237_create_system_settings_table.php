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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE system_settings ADD CONSTRAINT chk_system_settings_type CHECK (type IN ('string', 'integer', 'boolean', 'json'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE system_settings DROP CONSTRAINT IF EXISTS chk_system_settings_type");
        Schema::dropIfExists('system_settings');
    }
};
