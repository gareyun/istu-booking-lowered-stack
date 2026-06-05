<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;
use App\Models\BuildingType;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $building_types = BuildingType::all();

        $buildings = Building::count() < 3 ? [
            Building::create([
                'building_type_id' => $building_types->random()->id,
                'name' => '3 корпус',
                'address' => 'ул. Студенческая',
                'description' => 'Корпус для учашихся на IT'
            ]),
            Building::create([
                'building_type_id' => $building_types->random()->id,
                'name' => '1 корпус',
                'address' => 'ул. Студенческая',
                'description' => 'Корпус для учашихся на физике'
            ]),
            Building::create([
                'building_type_id' => 3,
                'name' => 'СЦ Интеграл',
                'address' => 'ул. Студенческая',
                'description' => 'Корпус творчества'
            ])
        ] : Building::take(3)->get();
    }
}
