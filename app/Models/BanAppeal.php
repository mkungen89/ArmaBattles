<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanAppeal extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'appeal_message',
        'status',
        'reviewed_by',
        'admin_response',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who submitted the appeal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the appeal
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if appeal is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if appeal is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if appeal is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope for pending appeals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
