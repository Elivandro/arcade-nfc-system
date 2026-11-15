<?php

namespace Database\Seeders;

use App\Models\ArcadeMachine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArcadeMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArcadeMachine::factory()->create([
            'name' =>   "Metal Slug",
            'api_token' =>  "e5a0e301-1c14-396e-9921-895a95baa121",
            'is_online' =>  true,
            'cost_per_play' => 1.00,
        ]);

        ArcadeMachine::factory()->create([
            'name' =>   "KOF 2000",
            'api_token' =>  "70384481-c02c-3a74-8531-12358c688f4a",
            'is_online' =>  true,
            'cost_per_play' => 2.00,
        ]);

        ArcadeMachine::factory()->create([
            'name' =>   "KOF 2002",
            'api_token' =>  "48065d5a-8b59-3a56-85b1-9f8e718c8901",
            'is_online' =>  true,
            'cost_per_play' => 1.50,
        ]);
    }
}
