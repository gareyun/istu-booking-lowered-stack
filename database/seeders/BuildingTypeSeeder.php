<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BuildingType;

class BuildingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $building_types = BuildingType::count() < 3 ? [
            BuildingType::create([
                'type' => 'Учебный корпус'
            ]),
            BuildingType::create([
                'type' => 'Студенческий центр'
            ]),
            BuildingType::create([
                'type' => 'Стадион'
            ])
        ] : BuildingType::take(3)->get();
    }
}
