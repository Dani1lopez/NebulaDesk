<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'subject',
        'description',
        'status',
        'priority',
        'requester_id',
        'organization_id',
        'assignee_id',
        'sla_due_date',
        'sla_breached',
    ];

    protected $casts = [
        'sla_due_date' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
