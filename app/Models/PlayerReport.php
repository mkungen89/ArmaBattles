<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerReport extends Model
{
    protected $fillable = [
        'server_id',
        'reporter_name',
        'reporter_uuid',
        'reporter_id',
        'target_name',
        'reason',
        'channel',
        'status',
        'admin_notes',
        'handled_by',
        'handled_at',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForTarget($query, string $name)
    {
        return $query->where('target_name', $name);
    }
}
