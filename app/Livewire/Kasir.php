<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Services\CheckoutService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Kasir extends Component
{
    public string $q = '';

    public string $cat = 'Semua';

    public string $channel = 'ONLINE'; // ONLINE (Shopee) | OFFLINE

    public string $saleDate = '';

    public string $method = 'SHOPEE'; // SHOPEE | TUNAI | TRANSFER | QRIS

    /** @var array<int,array> keyed by variant id */
    public array $cart = [];

    public ?int $picking = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->saleDate = now()->format('Y-m-d');
    }

    public function updatedChannel(): void
    {
        $this->method = $this->channel === 'ONLINE' ? 'SHOPEE' : 'TUNAI';
    }

    #[Computed]
    public function products()
    {
        return Product::with(['category', 'attributes.options', 'variants' => fn ($q) => $q->where('is_active', true)])
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

    public function priceOf(array $line): int
    {
        return $this->channel === 'ONLINE' ? $line['online'] : $line['offline'];
    }

    #[Computed]
    public function totals(): array
    {
        $s = StoreSetting::current();
        $count = 0;
        $subtotal = 0;
        $cost = 0;
        $shopeeFee = 0;
        foreach ($this->cart as $line) {
            $count += $line['qty'];
            $subtotal += $this->priceOf($line) * $line['qty'];
            $cost += ($line['cost'] ?? 0) * $line['qty'];
            if ($this->channel === 'ONLINE') {
                $shopeeFee += (int) round($line['online'] * $line['qty'] * ($line['feeRate'] ?? 0));
            }
        }
        $tax = (int) round($subtotal * $s->tax_rate);
        $total = $subtotal + $tax;
        $profit = $subtotal - $cost - $shopeeFee;

        return compact('count', 'subtotal', 'tax', 'total', 'cost', 'shopeeFee', 'profit') + ['taxRate' => $s->tax_rate];
    }

    // ---------- Katalog ----------
    public function pick(int $productId): void
    {
        $product = $this->products->firstWhere('id', $productId);
        if (! $product) {
            return;
        }
        if ($product->attributes->count() === 0) {
            $this->add($product->variants->first()->id);

            return;
        }
        $this->picking = $productId;
    }

    public function add(int $variantId): void
    {
        $v = ProductVariant::with('product.category')->find($variantId);
        if (! $v || $v->stock <= 0) {
            return;
        }
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
        } else {
            $this->cart[$variantId] = [
                'variant_id' => $variantId, 'product' => $v->product->name,
                'emoji' => $v->product->emoji ?? '📦', 'label' => $v->label,
                'offline' => $v->offline_price, 'online' => $v->online_price,
                'cost' => $v->cost_price, 'feeRate' => $v->product->category?->shopeeFeeRate() ?? 0.0,
                'stock' => $v->stock, 'qty' => 1,
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

    public function inc(int $variantId): void
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    // ---------- Simpan ----------
    public function save(CheckoutService $svc): void
    {
        $this->error = null;
        if (empty($this->cart)) {
            $this->error = 'Belum ada item.';

            return;
        }
        $res = $svc->record($this->cart, $this->channel, $this->method, $this->saleDate);
        if (! $res['ok']) {
            $this->error = $res['error'];

            return;
        }
        $this->cart = [];
        unset($this->products, $this->filtered);
        $this->dispatch('toast', message: 'Penjualan tercatat · '.$res['code'], type: 'success');
    }

    public function render()
    {
        return view('livewire.kasir');
    }
}
