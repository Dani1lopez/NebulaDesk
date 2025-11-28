<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SLASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slas = [
            ['priority' => 'urgent', 'response_time_hours' => 1, 'resolution_time_hours' => 4],
            ['priority' => 'high', 'response_time_hours' => 2, 'resolution_time_hours' => 8],
            ['priority' => 'medium', 'response_time_hours' => 4, 'resolution_time_hours' => 24],
            ['priority' => 'low', 'response_time_hours' => 8, 'resolution_time_hours' => 48],
        ];

        foreach ($slas as $sla) {
            DB::table('slas')->updateOrInsert(
                ['priority' => $sla['priority']],
                $sla
            );
        }
    }
}
