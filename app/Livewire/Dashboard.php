<?php

namespace App\Livewire;

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
    public function today(): array
    {
        $from = now()->startOfDay();
        $agg = Transaction::where('created_at', '>=', $from)->where('status', 'COMPLETED')
            ->selectRaw('COALESCE(SUM(total),0) as penjualan, COALESCE(SUM(profit),0) as laba, COUNT(*) as jml')
            ->first();
        $items = TransactionItem::whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $from)->where('status', 'COMPLETED'))->sum('qty');

        return ['penjualan' => (int) $agg->penjualan, 'laba' => (int) $agg->laba, 'jml' => (int) $agg->jml, 'item' => (int) $items];
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
    public function recent()
    {
        return Transaction::with('member')->latest('id')->limit(5)->get();
    }

    #[Computed]
    public function topToday()
    {
        $from = now()->startOfDay();

        return TransactionItem::whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $from)->where('status', 'COMPLETED'))
            ->selectRaw('name_snapshot, SUM(qty) as qty')
            ->groupBy('name_snapshot')->orderByDesc('qty')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
