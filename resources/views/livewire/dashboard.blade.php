@php $inv = $this->inventory; $t = $this->today; @endphp
<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-6 px-5 py-7 pb-24 md:pb-7">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }} 👋</h1>
                <p class="mt-0.5 text-sm text-ink-soft">{{ now()->translatedFormat('l, d F Y') }} · ringkasan inventori</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <a href="/produk" wire:navigate class="rounded-2xl border border-line px-4 py-3 text-sm font-semibold text-ink-soft transition hover:bg-surface-3">+ Produk</a>
                <a href="/stok" wire:navigate class="flex items-center gap-2 rounded-2xl bg-accent-600 px-5 py-3 text-sm font-bold text-white shadow-card transition hover:bg-accent-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    Kelola Stok
                </a>
            </div>
        </div>

        @if ($inv['invValue'] === 0 && $inv['variants'] > 0)
            <div class="flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                <svg class="h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v5M12 16v.5M12 3l9 16H3z"/></svg>
                <span>Belum ada stok tercatat. Isi stok tiap produk lewat menu <a href="/stok" wire:navigate class="font-bold underline">Stok</a> agar nilai inventaris & laporan berjalan.</span>
            </div>
        @endif

        {{-- KPI inventori --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @php
                $kpis = [
                    ['Nilai inventaris', rp($inv['invValue']), 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6', 'chip-accent'],
                    ['Produk aktif', number_format($inv['products']).' · '.number_format($inv['variants']).' varian', 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7', 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400'],
                    ['Perlu restock', number_format($inv['lowStock']).' varian', 'M12 8v5M12 16v.5M12 3l9 16H3z', $inv['lowStock'] > 0 ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-surface-3 text-ink-faint'],
                    ['Stok habis', number_format($inv['outStock']).' varian', 'M18 6L6 18M6 6l12 12', $inv['outStock'] > 0 ? 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400' : 'bg-surface-3 text-ink-faint'],
                ];
            @endphp
            @foreach ($kpis as [$lab, $val, $icon, $chip])
                <div class="card animate-slide-up p-4">
                    <div class="stat-ico {{ $chip }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $icon }}"/></svg>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-ink-soft">{{ $lab }}</div>
                    <div class="mt-0.5 text-lg font-extrabold tracking-tight tabular-nums text-ink lg:text-xl">{{ $val }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            {{-- Produk per kategori --}}
            <section class="card p-5">
                <div class="mb-4 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Produk per Kategori</h2>
                    <a href="/produk" wire:navigate class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Kelola →</a>
                </div>
                @php $maxCat = max(1, $this->categoryDist->max('count')); @endphp
                @if ($this->categoryDist->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-faint">Belum ada kategori.</p>
                @else
                    <div class="flex flex-col gap-2.5">
                        @foreach ($this->categoryDist as $c)
                            <div class="flex items-center gap-3">
                                <span class="w-28 flex-none truncate text-xs font-semibold text-ink-soft" title="{{ $c['name'] }}">{{ $c['name'] }}</span>
                                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-surface-3">
                                    <div class="h-full rounded-full bg-accent-500 transition-all" style="width: {{ max(4, round($c['count'] / $maxCat * 100)) }}%"></div>
                                </div>
                                <span class="w-8 flex-none text-right text-xs font-bold tabular-nums">{{ $c['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Perlu restock --}}
            <section class="card p-5">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Perlu Restock</h2>
                    @if ($this->lowStock->isNotEmpty())<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">{{ $inv['lowStock'] }}</span>@endif
                    <a href="/stok" wire:navigate class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Stok →</a>
                </div>
                @if ($this->lowStock->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-faint">Semua stok aman 👍</p>
                @else
                    <ul class="flex flex-col gap-1">
                        @foreach ($this->lowStock as $v)
                            <li class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm transition hover:bg-surface-2">
                                <span class="text-base">{{ $v->product->emoji ?? '📦' }}</span>
                                <span class="min-w-0 flex-1 truncate">{{ $v->product->name }}{{ $v->label ? ' · '.$v->label : '' }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $v->stock === 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' }}">{{ $v->stock }} {{ $v->product->unit }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Ringkasan penjualan (sekunder) --}}
        <section class="card p-5">
            <div class="mb-3 flex items-center gap-2">
                <h2 class="text-sm font-bold">Penjualan Hari Ini</h2>
                <a href="/laporan" wire:navigate class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Laporan →</a>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div><div class="text-xs font-semibold text-ink-soft">Penjualan</div><div class="mt-0.5 text-lg font-extrabold tabular-nums">{{ rp($t['penjualan']) }}</div></div>
                <div><div class="text-xs font-semibold text-ink-soft">Laba</div><div class="mt-0.5 text-lg font-extrabold tabular-nums text-emerald-600 dark:text-emerald-400">{{ rp($t['laba']) }}</div></div>
                <div><div class="text-xs font-semibold text-ink-soft">Transaksi</div><div class="mt-0.5 text-lg font-extrabold tabular-nums">{{ number_format($t['jml']) }}</div></div>
            </div>
            @if ($this->recent->isNotEmpty())
                <ul class="mt-3 divide-y divide-line-soft border-t border-line-soft pt-1">
                    @foreach ($this->recent as $tr)
                        <li class="flex items-center gap-3 py-2 text-sm">
                            <span class="font-semibold tabular-nums">{{ $tr->code }}</span>
                            <span class="text-xs text-ink-faint">{{ $tr->created_at->translatedFormat('d M, H.i') }}</span>
                            <span class="rounded-md bg-surface-3 px-1.5 py-0.5 text-[11px] font-semibold text-ink-soft">{{ $tr->channel === 'ONLINE' ? 'Shopee' : 'Offline' }}</span>
                            <span class="ml-auto font-semibold tabular-nums">{{ rp($tr->total) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
