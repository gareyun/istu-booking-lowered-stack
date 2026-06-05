<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomCategory extends Model
{
    protected $fillable = ['category'];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}