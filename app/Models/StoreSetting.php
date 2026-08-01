<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'logo_path', 'tax_rate', 'member_discount_rate', 'point_per_rupiah',
    ];

    protected $casts = [
        'tax_rate' => 'float',
        'member_discount_rate' => 'float',
        'point_per_rupiah' => 'float',
    ];

    /** Ambil baris pengaturan tunggal (id=1), buat default bila belum ada. */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
