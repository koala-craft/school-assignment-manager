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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            
            // Add indexes
            $table->index('academic_year_id');
            $table->unique(['academic_year_id', 'name']);
        });
        
        // Add CHECK constraint
        DB::statement("ALTER TABLE terms ADD CONSTRAINT chk_terms_dates CHECK (start_date < end_date)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE terms DROP CONSTRAINT IF EXISTS chk_terms_dates");
        Schema::dropIfExists('terms');
    }
};
