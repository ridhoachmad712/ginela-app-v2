<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Dashboard extends Component
{
    #[Computed]
    public function inventory(): array
    {
        $variants = ProductVariant::where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get(['stock', 'cost_price', 'min_stock']);

        return [
            'invValue' => (int) $variants->sum(fn ($v) => $v->stock * $v->cost_price),
            'products' => Product::where('is_active', true)->count(),
            'variants' => $variants->count(),
            'lowStock' => $variants->filter(fn ($v) => $v->stock <= $v->min_stock)->count(),
            'outStock' => $variants->filter(fn ($v) => $v->stock <= 0)->count(),
        ];
    }

    #[Computed]
    public function categoryDist()
    {
        return Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('products_count')->get()
            ->map(fn ($c) => ['name' => $c->name, 'count' => (int) $c->products_count]);
    }

    #[Computed]
    public function lowStock()
    {
        return ProductVariant::with('product')
            ->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')->limit(6)->get();
    }

    #[Computed]
    public function today(): array
    {
        $from = now()->startOfDay();
        $agg = Transaction::where('created_at', '>=', $from)
            ->selectRaw('COALESCE(SUM(total),0) as penjualan, COALESCE(SUM(profit),0) as laba, COUNT(*) as jml')
            ->first();

        return ['penjualan' => (int) $agg->penjualan, 'laba' => (int) $agg->laba, 'jml' => (int) $agg->jml];
    }

    #[Computed]
    public function recent()
    {
        return Transaction::latest('id')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
