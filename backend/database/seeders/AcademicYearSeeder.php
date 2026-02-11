<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        
        // Create current academic year
        $academicYearId = DB::table('academic_years')->insertGetId([
            'year' => $currentYear,
            'name' => "{$currentYear}年度",
            'start_date' => "{$currentYear}-04-01",
            'end_date' => ($currentYear + 1) . "-03-31",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create terms for current year
        DB::table('terms')->insert([
            [
                'academic_year_id' => $academicYearId,
                'name' => '前期',
                'start_date' => "{$currentYear}-04-01",
                'end_date' => "{$currentYear}-09-30",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'academic_year_id' => $academicYearId,
                'name' => '後期',
                'start_date' => "{$currentYear}-10-01",
                'end_date' => ($currentYear + 1) . "-03-31",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
