<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Laporan extends Component
{
    public string $range = '7d'; // today | 7d | 30d

    private function days(): int
    {
        return match ($this->range) {
            'today' => 1,
            '30d' => 30,
            default => 7,
        };
    }

    private function from(): Carbon
    {
        return now()->startOfDay()->subDays($this->days() - 1);
    }

    private function scope($q)
    {
        return $q->where('created_at', '>=', $this->from())->where('status', 'COMPLETED');
    }

    #[Computed]
    public function stats(): array
    {
        $agg = Transaction::query()->tap(fn ($q) => $this->scope($q))
            ->selectRaw('COALESCE(SUM(total),0) as penjualan, COALESCE(SUM(profit),0) as laba, COUNT(*) as jml')
            ->first();
        $items = TransactionItem::whereHas('transaction', fn ($q) => $this->scope($q))->sum('qty');

        return [
            'penjualan' => (int) $agg->penjualan, 'laba' => (int) $agg->laba,
            'jml' => (int) $agg->jml, 'item' => (int) $items,
        ];
    }

    #[Computed]
    public function daily(): array
    {
        $rows = Transaction::query()->tap(fn ($q) => $this->scope($q))->get(['created_at', 'total']);
        $days = $this->days();
        $buckets = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $this->from()->copy()->addDays($i);
            $buckets[$d->format('Y-m-d')] = ['label' => $d->translatedFormat($days > 7 ? 'j/n' : 'd M'), 'total' => 0];
        }
        foreach ($rows as $r) {
            $k = $r->created_at->format('Y-m-d');
            if (isset($buckets[$k])) {
                $buckets[$k]['total'] += $r->total;
            }
        }

        return array_values($buckets);
    }

    #[Computed]
    public function topProducts()
    {
        return TransactionItem::whereHas('transaction', fn ($q) => $this->scope($q))
            ->selectRaw('name_snapshot, SUM(qty) as qty, SUM(line_total) as revenue')
            ->groupBy('name_snapshot')->orderByDesc('qty')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.laporan');
    }
}
