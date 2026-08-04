<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class ProdukDetail extends Component
{
    public Product $product;

    public bool $isAdmin = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['category', 'attributes', 'variants']);
        $this->isAdmin = (bool) auth()->user()?->isAdmin();
    }

    public function render()
    {
        $variants = $this->product->variants;

        $summary = [
            'variants' => $variants->count(),
            'stock' => (int) $variants->sum('stock'),
            'invValue' => (int) $variants->sum(fn ($v) => $v->stock * $v->cost_price),
            'lowStock' => $variants->filter(fn ($v) => $v->stock <= $v->min_stock)->count(),
        ];

        $feeRate = $this->product->category?->shopeeFeeRate() ?? 0.0;

        return view('livewire.produk-detail', compact('variants', 'summary', 'feeRate'));
    }
}
