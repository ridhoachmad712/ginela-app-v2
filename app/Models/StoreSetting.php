<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'logo_path', 'theme_color', 'tax_rate', 'member_discount_rate', 'point_per_rupiah', 'offline_discount_rate',
    ];

    protected $casts = [
        'tax_rate' => 'float',
        'member_discount_rate' => 'float',
        'point_per_rupiah' => 'float',
        'offline_discount_rate' => 'float',
    ];

    /** Ambil baris pengaturan tunggal (id=1), buat default bila belum ada. */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
