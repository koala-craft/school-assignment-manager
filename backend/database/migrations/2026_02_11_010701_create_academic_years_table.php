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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            // Add indexes
            $table->index('is_active');
        });
        
        // Add CHECK constraints
        DB::statement("ALTER TABLE academic_years ADD CONSTRAINT chk_academic_years_dates CHECK (start_date < end_date)");
        DB::statement("ALTER TABLE academic_years ADD CONSTRAINT chk_academic_years_year CHECK (year BETWEEN 2000 AND 2100)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE academic_years DROP CONSTRAINT IF EXISTS chk_academic_years_dates");
        DB::statement("ALTER TABLE academic_years DROP CONSTRAINT IF EXISTS chk_academic_years_year");
        Schema::dropIfExists('academic_years');
    }
};
