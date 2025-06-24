<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $table = 'categories';

    const LIMIT_TYPE_QUOTA = 'quota';
    const LIMIT_TYPE_AMOUNT = 'amount';

    protected $fillable = [
        'name',
        'limit_type',
        'limit_value'
    ];

    protected function casts(): array
    {
        return [
            'limit_value' => 'decimal:2',
        ];
    }

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function isQuotaType()
    {
        return $this->limit_type === self::LIMIT_TYPE_QUOTA;
    }

    public function isAmountType()
    {
        return $this->limit_type === self::LIMIT_TYPE_AMOUNT;
    }
}
