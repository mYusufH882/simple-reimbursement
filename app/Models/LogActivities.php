<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivities extends Model
{
    const TYPE_NEW_REQUEST = 'new_request';
    const TYPE_CHANGE_STATUS = 'change_status';
    const TYPE_CHANGE_APPROVAL = 'change_approval';

    protected $fillable = [
        'action',
        'type',
        'old_value',
        'new_value',
        'loggable_type',
        'loggable_id',
        'reimbursement_id',
        'ip_address',
        'user_agent',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function reimbursement()
    {
        return $this->belongsTo(Reimbursement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isNewRequest()
    {
        return $this->type === self::TYPE_NEW_REQUEST;
    }

    public function isStatusChange()
    {
        return $this->type === self::TYPE_CHANGE_STATUS;
    }

    public function isApprovalChange()
    {
        return $this->type === self::TYPE_CHANGE_APPROVAL;
    }
}
