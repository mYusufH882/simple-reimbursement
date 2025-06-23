<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proof extends Model
{
    protected $fillable = [
        'file_path',
        'file_name',
        'file_type',
        'reimbursement_id',
    ];

    public function reimbursement()
    {
        return $this->belongsTo(Reimbursement::class);
    }
}
