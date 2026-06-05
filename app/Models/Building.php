<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = [
        'building_type_id',
        'name',
        'address',
        'description'
    ];

    public function type()
    {
        return $this->belongsTo(BuildingType::class, 'building_type_id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}