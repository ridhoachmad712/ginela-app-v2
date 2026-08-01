<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['name', 'phone', 'points'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
