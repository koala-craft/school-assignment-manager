<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        $academicYear = DB::table('academic_years')->where('year', $currentYear)->first();
        $firstTerm = DB::table('terms')->where('academic_year_id', $academicYear->id)->where('name', '前期')->first();
        $secondTerm = DB::table('terms')->where('academic_year_id', $academicYear->id)->where('name', '後期')->first();
        
        // Teacher IDs
        $teacher1 = DB::table('users')->where('email', 'yamada@school.local')->first()->id;
        $teacher2 = DB::table('users')->where('email', 'tanaka@school.local')->first()->id;

        // Create subjects for first term
        $subject1 = DB::table('subjects')->insertGetId([
            'code' => 'CS101',
            'name' => 'Programming Fundamentals',
            'academic_year_id' => $academicYear->id,
            'term_id' => $firstTerm->id,
            'description' => 'Learn the basics of programming',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subject2 = DB::table('subjects')->insertGetId([
            'code' => 'CS102',
            'name' => 'Database Systems',
            'academic_year_id' => $academicYear->id,
            'term_id' => $firstTerm->id,
            'description' => 'Introduction to database design and SQL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign teachers to subjects
        DB::table('subject_teachers')->insert([
            ['subject_id' => $subject1, 'teacher_id' => $teacher1, 'created_at' => now(), 'updated_at' => now()],
            ['subject_id' => $subject2, 'teacher_id' => $teacher2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Enroll students
        $students = DB::table('users')->where('role', 'student')->orWhere('role', 'student_admin')->get();
        
        foreach ($students as $student) {
            DB::table('enrollments')->insert([
                ['subject_id' => $subject1, 'student_id' => $student->id, 'enrolled_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['subject_id' => $subject2, 'student_id' => $student->id, 'enrolled_at' => now(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
