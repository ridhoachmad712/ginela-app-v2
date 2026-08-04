@php $t = $this->today; @endphp
<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-6 px-5 py-7 pb-24 md:pb-7">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }} 👋</h1>
                <p class="mt-0.5 text-sm text-ink-soft">{{ now()->translatedFormat('l, d F Y') }} · ringkasan hari ini</p>
            </div>
            <a href="/kasir" class="ml-auto flex items-center gap-2 rounded-2xl bg-accent-600 px-5 py-3 text-sm font-bold text-white shadow-card transition hover:bg-accent-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Catat Transaksi
            </a>
        </div>

        {{-- Ringkasan hari ini --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @php
                $stats = [
                    ['Penjualan hari ini', rp($t['penjualan']), 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6', 'chip-accent'],
                    ['Laba hari ini', rp($t['laba']), 'M3 17l6-6 4 4 8-8M17 7h4v4', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
                    ['Transaksi', number_format($t['jml']), 'M9 2v4M15 2v4M4 5h16v17H4zM8 11h8M8 15h5', 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400'],
                    ['Item terjual', number_format($t['item']), 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
                ];
            @endphp
            @foreach ($stats as [$lab, $val, $icon, $chip])
                <div class="card animate-slide-up p-4">
                    <div class="stat-ico {{ $chip }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $icon }}"/></svg>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-ink-soft">{{ $lab }}</div>
                    <div class="mt-0.5 text-2xl font-extrabold tracking-tight tabular-nums text-ink">{{ $val }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            {{-- Stok menipis --}}
            <section class="card p-5">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Stok Menipis</h2>
                    @if ($this->lowStock->isNotEmpty())<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">{{ $this->lowStock->count() }}</span>@endif
                    <a href="/stok" class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Kelola →</a>
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

            {{-- Terlaris hari ini --}}
            <section class="card p-5">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Terlaris Hari Ini</h2>
                    <a href="/laporan" class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Laporan →</a>
                </div>
                @if ($this->topToday->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-faint">Belum ada penjualan hari ini.</p>
                @else
                    <ul class="flex flex-col gap-1">
                        @foreach ($this->topToday as $i => $p)
                            <li class="flex items-center gap-3 rounded-xl px-2 py-1.5 text-sm transition hover:bg-surface-2">
                                <span class="grid h-6 w-6 flex-none place-items-center rounded-lg chip-accent text-xs font-bold">{{ $i + 1 }}</span>
                                <span class="min-w-0 flex-1 truncate font-medium">{{ $p->name_snapshot }}</span>
                                <span class="text-xs font-semibold tabular-nums text-ink-faint">{{ $p->qty }} terjual</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Transaksi terakhir --}}
        <section class="card p-5">
            <div class="mb-3 flex items-center gap-2">
                <h2 class="text-sm font-bold">Transaksi Terakhir</h2>
                <a href="/transaksi" class="ml-auto text-xs font-semibold text-accent-600 hover:underline">Semua →</a>
            </div>
            @if ($this->recent->isEmpty())
                <p class="py-6 text-center text-sm text-ink-faint">Belum ada transaksi.</p>
            @else
                <ul class="divide-y divide-line-soft">
                    @foreach ($this->recent as $tr)
                        <li class="flex items-center gap-3 py-2.5 text-sm">
                            <span class="font-semibold tabular-nums">{{ $tr->code }}</span>
                            <span class="text-xs text-ink-faint">{{ $tr->created_at->translatedFormat('d M, H.i') }}</span>
                            <span class="text-xs text-ink-faint">{{ $tr->member->name ?? 'Umum' }}</span>
                            <span class="ml-auto font-semibold tabular-nums">{{ rp($tr->total) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
