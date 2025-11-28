<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'created_at'
    ];

    public $timestamps = false; // We manually manage created_at

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
