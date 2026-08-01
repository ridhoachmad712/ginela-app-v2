<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Member;
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

    public string $channel = 'OFFLINE'; // OFFLINE | ONLINE

    /** @var array<int,array> keyed by variant id */
    public array $cart = [];

    public ?int $picking = null;

    // Pembayaran
    public bool $paying = false;

    public string $method = 'CASH';

    public int $received = 0;

    public ?int $memberId = null;

    public bool $pickMember = false;

    public string $memberQuery = '';

    public ?string $payError = null;

    public ?array $done = null; // struk setelah sukses

    public const QUICK_CASH = [50000, 100000, 150000, 200000];

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

    #[Computed]
    public function member()
    {
        return $this->memberId ? Member::find($this->memberId) : null;
    }

    #[Computed]
    public function memberList()
    {
        $s = trim(mb_strtolower($this->memberQuery));

        return Member::when($s !== '', fn ($q) => $q->whereRaw('LOWER(name) like ?', ["%{$s}%"])->orWhere('phone', 'like', "%{$s}%"))
            ->orderBy('name')->limit(20)->get();
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
        foreach ($this->cart as $line) {
            $count += $line['qty'];
            $subtotal += $this->priceOf($line) * $line['qty'];
        }
        $hasMember = (bool) $this->memberId;
        $discount = $hasMember ? (int) round($subtotal * $s->member_discount_rate) : 0;
        $after = $subtotal - $discount;
        $tax = (int) round($after * $s->tax_rate);
        $total = $after + $tax;
        $points = $hasMember ? (int) floor($after * $s->point_per_rupiah) : 0;

        return compact('count', 'subtotal', 'discount', 'tax', 'total', 'points')
            + ['taxRate' => $s->tax_rate, 'discRate' => $s->member_discount_rate];
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
        $v = ProductVariant::with('product')->find($variantId);
        if (! $v || $v->stock <= 0) {
            return;
        }
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
        } else {
            $this->cart[$variantId] = [
                'variant_id' => $variantId, 'product' => $v->product->name,
                'emoji' => $v->product->emoji ?? '📦', 'label' => $v->label,
                'offline' => $v->offline_price, 'online' => $v->online_price, 'qty' => 1,
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
        $this->memberId = null;
    }

    // ---------- Pembayaran ----------
    public function openPayment(): void
    {
        if (empty($this->cart)) {
            return;
        }
        $this->paying = true;
        $this->method = 'CASH';
        $this->received = 0;
        $this->payError = null;
    }

    public function closePayment(): void
    {
        $this->paying = false;
    }

    public function setMember(int $id): void
    {
        $this->memberId = $id;
        $this->pickMember = false;
    }

    public function clearMember(): void
    {
        $this->memberId = null;
    }

    public function tapDigit(string $d): void
    {
        $this->received = (int) min((int) ($this->received.$d), 999999999);
    }

    public function setCash(int $v): void
    {
        $this->received = $v;
    }

    public function exactCash(): void
    {
        $this->received = $this->totals['total'];
    }

    public function delDigit(): void
    {
        $this->received = (int) floor($this->received / 10);
    }

    public function confirm(CheckoutService $svc): void
    {
        $this->payError = null;
        $t = $this->totals;
        if ($this->method === 'CASH' && $this->received < $t['total']) {
            $this->payError = 'Nominal tunai kurang.';

            return;
        }
        $paid = $this->method === 'CASH' ? $this->received : 0;
        $res = $svc->checkout($this->cart, $this->channel, $this->memberId, $this->method, $paid);
        if (! $res['ok']) {
            $this->payError = $res['error'];

            return;
        }
        $this->done = $res['receipt'];
        $this->cart = [];
        $this->memberId = null;
    }

    public function newTransaction(): void
    {
        $this->done = null;
        $this->paying = false;
        $this->received = 0;
        unset($this->products, $this->filtered); // refresh stok
    }

    public function render()
    {
        return view('livewire.kasir');
    }
}
