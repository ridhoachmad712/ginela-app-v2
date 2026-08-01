<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'label', 'offline_price', 'online_price',
        'cost_price', 'stock', 'min_stock', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->belongsToMany(AttributeOption::class, 'variant_options', 'variant_id', 'option_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    /** Harga sesuai channel. */
    public function priceFor(string $channel): int
    {
        return $channel === 'ONLINE' ? $this->online_price : $this->offline_price;
    }
}
