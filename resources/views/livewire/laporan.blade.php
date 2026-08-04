@php
    $ranges = ['today' => 'Hari ini', '7d' => '7 Hari', '30d' => '30 Hari'];
    $s = $this->stats;
    $daily = $this->daily;
    $maxDay = max(1, ...array_map(fn ($b) => $b['total'], $daily));
    $top = $this->topProducts;
    $maxQty = max(1, ...($top->pluck('qty')->all() ?: [1]));
@endphp

<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Laporan</h1>
                <p class="text-sm text-ink-soft">Ringkasan penjualan &amp; laba toko</p>
            </div>
            <div class="ml-auto flex rounded-xl border border-line bg-surface p-1">
                @foreach ($ranges as $rk => $rl)
                    <button wire:click="$set('range','{{ $rk }}')" class="rounded-lg px-3.5 py-1.5 text-sm font-semibold transition {{ $range === $rk ? 'bg-accent-600 text-white' : 'text-ink-soft' }}">{{ $rl }}</button>
                @endforeach
            </div>
        </div>

        {{-- Kartu statistik --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['Penjualan', rp($s['penjualan']), 'bg-accent-50 text-accent-600'], ['Laba kotor', rp($s['laba']), 'bg-green-50 text-green-600'], ['Transaksi', number_format($s['jml']), 'bg-surface-3 text-ink-soft'], ['Item terjual', number_format($s['item']), 'bg-surface-3 text-ink-soft']] as [$lab, $val, $cls])
                <div class="card p-4">
                    <div class="mb-2 inline-grid h-8 w-8 place-items-center rounded-lg {{ $cls }} text-xs font-bold">Rp</div>
                    <div class="text-xs font-medium text-ink-soft">{{ $lab }}</div>
                    <div class="mt-0.5 text-xl font-extrabold tracking-tight tabular-nums">{{ $val }}</div>
                </div>
            @endforeach
        </div>

        {{-- Tren harian --}}
        <section class="card p-5">
            <h2 class="mb-4 text-sm font-bold">Tren Penjualan Harian</h2>
            @if ($s['penjualan'] === 0)
                <p class="py-8 text-center text-sm text-ink-faint">Belum ada penjualan pada periode ini.</p>
            @else
                <div class="flex items-end gap-2 overflow-x-auto pb-1" style="height:180px">
                    @foreach ($daily as $b)
                        <div class="flex min-w-[28px] flex-1 flex-col items-center gap-1.5">
                            <div class="flex w-full flex-1 items-end">
                                <div class="w-full rounded-t-md bg-accent-600" style="height: {{ max(2, $b['total'] / $maxDay * 100) }}%" title="{{ rp($b['total']) }}"></div>
                            </div>
                            <span class="whitespace-nowrap text-[10px] text-ink-faint">{{ $b['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Produk terlaris --}}
        <section class="card p-5">
            <h2 class="mb-4 text-sm font-bold">Produk Terlaris</h2>
            @if ($top->isEmpty())
                <p class="py-8 text-center text-sm text-ink-faint">Belum ada data.</p>
            @else
                <ul class="flex flex-col gap-3">
                    @foreach ($top as $i => $p)
                        <li class="flex items-center gap-3">
                            <span class="grid h-7 w-7 flex-none place-items-center rounded-lg bg-surface-3 text-xs font-bold text-ink-soft">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between text-sm">
                                    <span class="truncate font-semibold">{{ $p->name_snapshot }}</span>
                                    <span class="tabular-nums text-ink-faint">{{ $p->qty }} terjual · {{ rp($p->revenue) }}</span>
                                </div>
                                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-surface-3">
                                    <div class="h-full rounded-full bg-accent-600" style="width: {{ $p->qty / $maxQty * 100 }}%"></div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
