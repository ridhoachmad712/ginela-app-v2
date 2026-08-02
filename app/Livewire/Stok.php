<?php

namespace App\Livewire;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Stok extends Component
{
    public string $q = '';

    public ?int $modalVariantId = null;

    public string $modalMode = 'add'; // add | set

    public string $qtyInput = '';

    public string $noteInput = '';

    public ?string $error = null;

    #[Computed]
    public function variants()
    {
        $s = trim(mb_strtolower($this->q));

        return ProductVariant::with('product')
            ->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get()
            ->filter(function ($v) use ($s) {
                if ($s === '') {
                    return true;
                }
                $name = mb_strtolower($v->product->name.' '.$v->label);

                return str_contains($name, $s);
            })
            ->sortBy(fn ($v) => $v->product->name.' '.$v->label)
            ->values();
    }

    #[Computed]
    public function lowStock()
    {
        return $this->variants->filter(fn ($v) => $v->stock <= $v->min_stock);
    }

    #[Computed]
    public function movements()
    {
        return StockMovement::with(['variant.product', 'user'])->latest('id')->limit(60)->get();
    }

    #[Computed]
    public function modalVariant()
    {
        return $this->modalVariantId ? ProductVariant::with('product')->find($this->modalVariantId) : null;
    }

    public function openAdd(int $vid): void
    {
        $this->modalVariantId = $vid;
        $this->modalMode = 'add';
        $this->reset(['qtyInput', 'noteInput', 'error']);
    }

    public function openSet(int $vid): void
    {
        $this->modalVariantId = $vid;
        $this->modalMode = 'set';
        $this->reset(['qtyInput', 'noteInput', 'error']);
    }

    public function close(): void
    {
        $this->modalVariantId = null;
    }

    public function save(): void
    {
        $v = ProductVariant::find($this->modalVariantId);
        if (! $v) {
            return;
        }
        $this->error = null;
        $n = (int) $this->qtyInput;
        $uid = auth()->id();

        if ($this->modalMode === 'add') {
            if ($n <= 0) {
                $this->error = 'Jumlah harus lebih dari 0.';

                return;
            }
            DB::transaction(function () use ($v, $n, $uid) {
                $before = $v->stock;
                $after = $before + $n;
                $v->update(['stock' => $after]);
                StockMovement::create([
                    'variant_id' => $v->id, 'type' => 'PURCHASE', 'qty' => $n,
                    'stock_before' => $before, 'stock_after' => $after,
                    'note' => trim($this->noteInput) ?: null, 'user_id' => $uid,
                ]);
            });
        } else {
            if ($this->qtyInput === '' || $n < 0) {
                $this->error = 'Jumlah stok tidak valid.';

                return;
            }
            DB::transaction(function () use ($v, $n, $uid) {
                $before = $v->stock;
                if ($n === $before) {
                    return;
                }
                $v->update(['stock' => $n]);
                StockMovement::create([
                    'variant_id' => $v->id, 'type' => 'ADJUSTMENT', 'qty' => $n - $before,
                    'stock_before' => $before, 'stock_after' => $n,
                    'note' => trim($this->noteInput) ?: null, 'user_id' => $uid,
                ]);
            });
        }

        $wasAdd = $this->modalMode === 'add';
        $this->close();
        unset($this->variants, $this->lowStock, $this->movements);
        $this->dispatch('toast', message: $wasAdd ? 'Stok ditambahkan' : 'Stok disesuaikan', type: 'success');
    }

    public function render()
    {
        return view('livewire.stok');
    }
}
