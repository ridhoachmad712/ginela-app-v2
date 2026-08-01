<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Riwayat extends Component
{
    public string $range = '7d'; // today | 7d | 30d | all

    public string $q = '';

    public ?int $detailId = null;

    private function from(): ?Carbon
    {
        return match ($this->range) {
            'today' => now()->startOfDay(),
            '7d' => now()->startOfDay()->subDays(6),
            '30d' => now()->startOfDay()->subDays(29),
            default => null,
        };
    }

    #[Computed]
    public function transactions()
    {
        $s = trim(mb_strtolower($this->q));

        return Transaction::with(['items', 'member', 'cashier'])
            ->when($this->from(), fn ($q, $f) => $q->where('created_at', '>=', $f))
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->whereRaw('LOWER(code) like ?', ["%{$s}%"])
                ->orWhereHas('member', fn ($m) => $m->whereRaw('LOWER(name) like ?', ["%{$s}%"]))))
            ->latest('id')->limit(200)->get();
    }

    #[Computed]
    public function periodeTotal()
    {
        return $this->transactions->sum('total');
    }

    #[Computed]
    public function detail()
    {
        return $this->detailId
            ? Transaction::with(['items', 'member', 'cashier'])->find($this->detailId)
            : null;
    }

    public function render()
    {
        return view('livewire.riwayat');
    }
}
