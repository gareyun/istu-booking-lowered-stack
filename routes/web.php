<?php

use Illuminate\Support\Facades\Route;

use App\Http\Livewire\BookingForm;
use App\Http\Livewire\Admin\BookingAdminPanel;
use App\Http\Livewire\Admin\Classrooms;
use App\Http\Livewire\ClassroomSchedule;
use App\Http\Livewire\TechSupportPanel;

Route::get('/', BookingForm::class)->name('booking');
Route::get('/booking', BookingForm::class)->name('booking');

Route::get('/admin', BookingAdminPanel::class)
    ->name('admin');

Route::get('/admin/classrooms', Classrooms::class)
    ->name('admin.classrooms');

Route::get('/schedule', ClassroomSchedule::class)
    ->name('schedule');

Route::get('/tech-support', TechSupportPanel::class)
    ->name('tech-support');