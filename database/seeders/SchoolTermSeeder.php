<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolTermSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $schoolYearId = DB::table('school_years')
            ->where('name', '2025-2026')
            ->value('id');

        if (!$schoolYearId) {
            return;
        }

        DB::table('school_terms')->updateOrInsert(
            [
                'school_year_id' => $schoolYearId,
                'term_order' => 1,
            ],
            [
                'name' => '1st Semester',
                'is_active' => true,
                'starts_on' => '2025-06-01',
                'ends_on' => '2025-10-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('school_terms')->updateOrInsert(
            [
                'school_year_id' => $schoolYearId,
                'term_order' => 2,
            ],
            [
                'name' => '2nd Semester',
                'is_active' => false,
                'starts_on' => '2025-11-01',
                'ends_on' => '2026-03-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}