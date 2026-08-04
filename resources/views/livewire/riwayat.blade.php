@php
    $ranges = ['today' => 'Hari ini', '7d' => '7 Hari', '30d' => '30 Hari', 'all' => 'Semua'];
    $methodMeta = ['CASH' => ['Tunai', 'bg-green-100 text-green-700'], 'QRIS' => ['QRIS', 'bg-accent-100 text-accent-700'], 'CARD' => ['Kartu', 'bg-amber-100 text-amber-700']];
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex flex-col gap-3 border-b border-line bg-surface px-5 pb-3 pt-5">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-lg font-bold tracking-tight">Riwayat Transaksi</h1>
                <p class="text-sm text-ink-soft">{{ $this->transactions->count() }} transaksi · {{ rp($this->periodeTotal) }}</p>
            </div>
            <div class="ml-auto flex rounded-xl border border-line bg-surface p-1">
                @foreach ($ranges as $rk => $rl)
                    <button wire:click="$set('range','{{ $rk }}')" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $range === $rk ? 'bg-accent-600 text-white' : 'text-ink-soft' }}">{{ $rl }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex max-w-md items-center gap-2 rounded-xl border border-line bg-surface px-3">
            <svg class="h-4 w-4 flex-none text-ink-faint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
            <input wire:model.live.debounce.300ms="q" placeholder="Cari kode / nama member…" class="h-11 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0">
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-auto px-5 py-4 pb-24 md:pb-4">
        @if ($this->transactions->isEmpty())
            <div class="flex animate-fade-in flex-col items-center justify-center gap-3 py-20 text-center text-ink-faint">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-surface-3">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 2v4M15 2v4M4 5h16v17H4zM8 11h8M8 15h5"/></svg>
                </div>
                <p class="text-sm font-medium">Belum ada transaksi pada periode ini.<br>Transaksi dari kasir akan muncul di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto card">
                <table class="w-full min-w-[680px] border-collapse text-sm">
                    <thead><tr class="border-b border-line bg-surface-2 text-left text-xs uppercase tracking-wide text-ink-soft">
                        <th class="px-4 py-3 font-semibold">Kode</th><th class="px-4 py-3 font-semibold">Waktu</th><th class="px-4 py-3 font-semibold">Metode</th><th class="px-4 py-3 font-semibold">Member</th><th class="px-4 py-3 text-right font-semibold">Item</th><th class="px-4 py-3 text-right font-semibold">Total</th><th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($this->transactions as $t)
                            @php [$ml, $mc] = $methodMeta[$t->method] ?? [$t->method, 'bg-surface-3 text-ink-soft']; @endphp
                            <tr wire:key="t-{{ $t->id }}" class="cursor-pointer border-b border-line-soft last:border-0 hover:bg-surface-2" wire:click="$set('detailId', {{ $t->id }})">
                                <td class="px-4 py-3 font-semibold tabular-nums">{{ $t->code }}</td>
                                <td class="px-4 py-3 text-ink-soft">{{ $t->created_at->translatedFormat('d M, H.i') }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $mc }}">{{ $ml }}</span></td>
                                <td class="px-4 py-3 text-ink-soft">{{ $t->member->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-soft">{{ $t->items->sum('qty') }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ rp($t->total) }}</td>
                                <td class="px-4 py-3 text-right"><button wire:click.stop="$set('detailId', {{ $t->id }})" class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-semibold hover:bg-surface-3">Detail</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Detail + cetak ulang --}}
    @if ($this->detail)
        <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center print:block print:bg-surface">
            <div class="absolute inset-0 bg-black/50 print:hidden" wire:click="$set('detailId', null)"></div>
            <div class="relative flex max-h-[90vh] w-full flex-col rounded-t-3xl bg-surface sm:max-w-sm sm:rounded-3xl print:max-h-none print:rounded-none">
                <div class="flex items-center gap-2 border-b border-line px-5 py-4 print:hidden">
                    <h3 class="text-base font-bold">Detail Transaksi</h3>
                    <button wire:click="$set('detailId', null)" class="ml-auto text-ink-faint hover:text-ink">✕</button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto p-4">
                    @include('partials.receipt', ['trx' => $this->detail])
                </div>
                <div class="border-t border-line px-5 py-4 print:hidden">
                    <button onclick="window.print()" class="h-12 w-full rounded-xl bg-accent-600 font-semibold text-white hover:bg-accent-700">Cetak Ulang Struk</button>
                </div>
            </div>
        </div>
    @endif
</div>
