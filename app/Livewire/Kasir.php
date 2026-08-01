<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Kasir extends Component
{
    public string $q = '';

    public string $cat = 'Semua';

    public string $channel = 'OFFLINE'; // OFFLINE | ONLINE

    /** @var array<int,array> keyed by variant id */
    public array $cart = [];

    public ?int $picking = null; // product id sedang dipilih varian

    #[Computed]
    public function products()
    {
        return Product::with(['attributes.options', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('variants', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get()
            ->filter(fn ($p) => $p->variants->count() > 0);
    }

    #[Computed]
    public function categories()
    {
        return ['Semua', ...Category::orderBy('sort_order')->pluck('name')->all()];
    }

    #[Computed]
    public function filtered()
    {
        $s = trim(mb_strtolower($this->q));

        return $this->products->filter(function ($p) use ($s) {
            $okCat = $this->cat === 'Semua' || optional($p->category)->name === $this->cat;
            $okQ = $s === '' || str_contains(mb_strtolower($p->name), $s);

            return $okCat && $okQ;
        });
    }

    #[Computed]
    public function pickingProduct()
    {
        return $this->picking ? $this->products->firstWhere('id', $this->picking) : null;
    }

    public function price(array $line): int
    {
        return $this->channel === 'ONLINE' ? $line['online'] : $line['offline'];
    }

    #[Computed]
    public function totals(): array
    {
        $s = StoreSetting::current();
        $count = 0;
        $subtotal = 0;
        foreach ($this->cart as $line) {
            $count += $line['qty'];
            $subtotal += $this->price($line) * $line['qty'];
        }
        // Member menyusul (F2) — sementara tanpa diskon member.
        $discount = 0;
        $tax = (int) round(($subtotal - $discount) * $s->tax_rate);
        $total = $subtotal - $discount + $tax;

        return [
            'count' => $count, 'subtotal' => $subtotal, 'discount' => $discount,
            'tax' => $tax, 'total' => $total, 'taxRate' => $s->tax_rate,
        ];
    }

    public function pick(int $productId): void
    {
        $product = $this->products->firstWhere('id', $productId);
        if (! $product) {
            return;
        }
        // Produk tanpa atribut → langsung tambah varian default.
        if ($product->attributes->count() === 0) {
            $this->add($product->variants->first()->id);

            return;
        }
        $this->picking = $productId;
    }

    public function add(int $variantId): void
    {
        $v = ProductVariant::with('product')->find($variantId);
        if (! $v || $v->stock <= 0) {
            return;
        }
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
        } else {
            $this->cart[$variantId] = [
                'variant_id' => $variantId,
                'product' => $v->product->name,
                'emoji' => $v->product->emoji ?? '📦',
                'label' => $v->label,
                'offline' => $v->offline_price,
                'online' => $v->online_price,
                'qty' => 1,
            ];
        }
        $this->picking = null;
    }

    public function dec(int $variantId): void
    {
        if (! isset($this->cart[$variantId])) {
            return;
        }
        if (--$this->cart[$variantId]['qty'] <= 0) {
            unset($this->cart[$variantId]);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function render()
    {
        return view('livewire.kasir');
    }
}
