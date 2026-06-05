<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingType extends Model
{
    protected $fillable = ['type'];

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }
}