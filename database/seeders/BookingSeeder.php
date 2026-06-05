<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\User;
use \App\Models\Classroom;
use \App\Models\Booking;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $classrooms = Classroom::all();

        $bookings = Booking::count() < 3 ? [
            Booking::create([
                'user_id' => $users->random()->id,
                'classroom_id' => $classrooms->random()->id,
                'date' => '03.10.2026',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'purpose' => 'Собрание',
                'equipment' => 'Проектор',
                'is_tech_support' => true,
                'user_comment' => 'Хотим устроить чаепитие',
                'admin_comment' => 'Не пролейте',
                'google_event_id' => '',
                'status' => 'approved'
            ]),
            Booking::create([
                'user_id' => $users->random()->id,
                'classroom_id' => $classrooms->random()->id,
                'date' => '03.10.2026',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'purpose' => 'Собрание',
                'equipment' => 'Проектор',
                'is_tech_support' => true,
                'user_comment' => 'Перевыборы волонтёрского центра',
                'admin_comment' => 'УМП тоже придёт посмотреть!',
                'google_event_id' => '',
                'status' => 'approved'
            ]),
            Booking::create([
                'user_id' => $users->random()->id,
                'classroom_id' => $classrooms->random()->id,
                'date' => '03.10.2026',
                'start_time' => '17:30',
                'end_time' => '19:00',
                'purpose' => 'Собрание',
                'equipment' => '',
                'is_tech_support' => true,
                'user_comment' => 'Собрание студ совета',
                'admin_comment' => '',
                'google_event_id' => ''
            ])
        ] : Booking::take(3)->get();
    }
}
