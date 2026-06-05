<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            BuildingTypeSeeder::class,
            BuildingSeeder::class,
            ClassroomCategorySeeder::class,
            ClassroomSeeder::class,
            BookingSeeder::class
        ]);
    }
}
