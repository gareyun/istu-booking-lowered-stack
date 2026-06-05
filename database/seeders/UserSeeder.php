<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::count() < 3 ? [
            User::create([
                'name' => 'Иванов Александр Михайлович',
                'email' => 'mail1@example.com',
                'phone' => '+7 (950) 950-50-50',
                'password' => bcrypt('password'),
                'faculty' => 'ФИТ',
                'group' => 'Б22-191-2'
            ]),
            User::create([
                'name' => 'Александров Иван Алексеевич',
                'email' => 'mail2@example.com',
                'phone' => '+7 (950) 950-50-50',
                'password' => bcrypt('password'),
                'faculty' => 'ФИТ',
                'group' => 'Б22-191-1'
            ]),
            User::create([
                'name' => 'Алексеев Анатолий Юрьевич',
                'email' => 'mail3@example.com',
                'phone' => '+7 (950) 950-50-50',
                'password' => bcrypt('password'),
                'faculty' => 'ФСАиД',
                'group' => 'Б23-131-1'
            ])
        ] : User::get();
    }
}
