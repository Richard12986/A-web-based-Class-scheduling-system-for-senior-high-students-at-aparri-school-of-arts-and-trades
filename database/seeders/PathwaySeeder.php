<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PathwaySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('pathways')->updateOrInsert(
            ['code' => 'academic'],
            [
                'name' => 'Academic Track',
                'description' => 'Curriculum-defined academic pathway for senior high school.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('pathways')->updateOrInsert(
            ['code' => 'techpro'],
            [
                'name' => 'TechPro Track',
                'description' => 'Curriculum-defined technical-professional pathway for senior high school.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}