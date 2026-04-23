<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('grade_levels')->updateOrInsert(
            ['code' => '11'],
            [
                'name' => 'Grade 11',
                'sort_order' => 11,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('grade_levels')->updateOrInsert(
            ['code' => '12'],
            [
                'name' => 'Grade 12',
                'sort_order' => 12,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}