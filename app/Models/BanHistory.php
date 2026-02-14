<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanHistory extends Model
{
    protected $table = 'ban_history';

    protected $fillable = [
        'user_id',
        'action',
        'reason',
        'ban_type',
        'banned_until',
        'hardware_id',
        'ip_address',
        'actioned_by',
    ];

    protected $casts = [
        'banned_until' => 'datetime',
    ];

    /**
     * Get the user who was banned/unbanned
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who performed the action
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    /**
     * Get the action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'banned' => 'Banned',
            'unbanned' => 'Unbanned',
            'temp_ban_expired' => 'Temp Ban Expired',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get the ban type label
     */
    public function getBanTypeLabelAttribute(): string
    {
        return match($this->ban_type) {
            'permanent' => 'Permanent',
            'temporary' => 'Temporary',
            'hardware' => 'Hardware ID',
            'ip_range' => 'IP Range',
            default => 'Unknown',
        };
    }
}
