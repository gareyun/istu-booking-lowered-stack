<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassroomCategory;

class ClassroomCategorySeeder extends Seeder
{
    public function run(): void
    {
        $classroom_categories = ClassroomCategory::count() < 3 ? [
            ClassroomCategory::create([
                'category' => 'Учебная'
            ]),
            ClassroomCategory::create([
                'category' => 'Конференц-зал'
            ]),
            ClassroomCategory::create([
                'category' => 'Танцевальная'
            ])
        ] : ClassroomCategory::take(3)->get();
    }
}
