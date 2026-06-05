<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'classroom_id',
        'date',
        'start_time',
        'end_time',
        'purpose',
        'equipment',
        'is_tech_support',
        'user_comment',
        'admin_comment',
        'status',
        'google_event_id',
        'vk_link',
        'vk_user_id',
        'name',
        'faculty',
        'group',
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}