<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'sort_order', 'shopee_admin_rate', 'shopee_service_rate'];

    protected $casts = [
        'shopee_admin_rate' => 'float',
        'shopee_service_rate' => 'float',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Tarif fee Shopee efektif (pecahan). Bila kategori ini tak punya fee sendiri,
     * warisi dari induk terdekat yang punya. Mis. 0.1575.
     */
    public function shopeeFeeRate(): float
    {
        $own = (float) $this->shopee_admin_rate + (float) $this->shopee_service_rate;
        if ($own > 0) {
            return $own;
        }

        return $this->parent ? $this->parent->shopeeFeeRate() : 0.0;
    }

    /** Apakah fee di kategori ini diwarisi dari induk (bukan diatur sendiri). */
    public function feeIsInherited(): bool
    {
        return ((float) $this->shopee_admin_rate + (float) $this->shopee_service_rate) <= 0 && $this->parent_id !== null;
    }

    /** Daftar induk dari akar ke kategori ini (untuk breadcrumb). */
    public function ancestors(): array
    {
        $chain = [];
        $node = $this->parent;
        while ($node) {
            array_unshift($chain, $node);
            $node = $node->parent;
        }

        return $chain;
    }
}
