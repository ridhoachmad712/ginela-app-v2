<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'code', 'channel', 'cashier_id', 'member_id', 'subtotal', 'discount',
        'tax', 'total', 'cost', 'profit', 'paid', 'change', 'method', 'status',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
