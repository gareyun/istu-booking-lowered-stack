<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Classroom;
use \App\Models\ClassroomCategory;
use \App\Models\Building;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $calendars = config('services.calendar_ids');
        $classrooms_categories = ClassroomCategory::all();
        $buildings = Building::all();
        
        $classrooms = Classroom::count() < 3 ? [
            Classroom::create([
                'room' => '9-2',
                'classroom_category_id' => $classrooms_categories->random()->id,
                'building_id' => $buildings->random()->id,
                'description' => 'Конференц-зал в Интеграле',
                'equipment' => 'Ноутбук, проектор, флипчарт',
                'capacity' => '30',
                'google_calendar_id' => $calendars['9-2'] ?? null,
            ]),
            Classroom::create([
                'room' => '106',
                'classroom_category_id' => $classrooms_categories->random()->id,
                'building_id' => $buildings->random()->id,
                'description' => 'Волонтёрский кабинет',
                'equipment' => 'Канцелярия, флипчарт',
                'capacity' => '21',
                'google_calendar_id' => $calendars['9-2'] ?? null,
            ]),
            Classroom::create([
                'room' => 'Холл 2-го этажа',
                'classroom_category_id' => $classrooms_categories->random()->id,
                'building_id' => $buildings->random()->id,
                'description' => 'Холл для проведения ярмарок или выступлений',
                'equipment' => 'Ноутбук, аудиосистема, цифровой экран, микрофоны',
                'capacity' => '100',
                'google_calendar_id' => $calendars['floor_2'] ?? null,
            ])
        ] : Classroom::take(3)->get();
    }
}
