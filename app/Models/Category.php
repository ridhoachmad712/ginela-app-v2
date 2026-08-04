<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'sort_order', 'shopee_admin_rate', 'shopee_service_rate'];

    protected $casts = [
        'shopee_admin_rate' => 'float',
        'shopee_service_rate' => 'float',
    ];

    /** Total tarif potongan Shopee (admin + layanan) sebagai pecahan, mis. 0.1575. */
    public function shopeeFeeRate(): float
    {
        return (float) $this->shopee_admin_rate + (float) $this->shopee_service_rate;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
