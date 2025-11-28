<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLA extends Model
{
    protected $table = 'slas';

    protected $fillable = [
        'priority',
        'response_time_hours',
        'resolution_time_hours',
    ];

    protected $casts = [
        'response_time_hours' => 'integer',
        'resolution_time_hours' => 'integer',
    ];
}
