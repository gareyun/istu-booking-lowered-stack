<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'classroom_category_id',
        'building_id',
        'room',
        'description',
        'equipment',
        'capacity',
        'google_calendar_id'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function category()
    {
        return $this->belongsTo(ClassroomCategory::class, 'classroom_category_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}