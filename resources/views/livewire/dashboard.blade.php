@php $t = $this->today; @endphp
<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Halo, {{ auth()->user()->name }} 👋</h1>
                <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }} · ringkasan hari ini</p>
            </div>
            <a href="/kasir" class="ml-auto flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                Mulai Transaksi
            </a>
        </div>

        {{-- Ringkasan hari ini --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['Penjualan hari ini', rp($t['penjualan']), 'from-blue-600 to-sky-500'], ['Laba hari ini', rp($t['laba']), 'from-green-600 to-emerald-500'], ['Transaksi', number_format($t['jml']), 'from-slate-700 to-slate-500'], ['Item terjual', number_format($t['item']), 'from-slate-700 to-slate-500']] as [$lab, $val, $grad])
                <div class="rounded-2xl bg-gradient-to-br {{ $grad }} p-4 text-white">
                    <div class="text-xs font-medium opacity-90">{{ $lab }}</div>
                    <div class="mt-1 text-2xl font-extrabold tracking-tight tabular-nums">{{ $val }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            {{-- Stok menipis --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Stok Menipis</h2>
                    @if ($this->lowStock->isNotEmpty())<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $this->lowStock->count() }}</span>@endif
                    <a href="/stok" class="ml-auto text-xs font-semibold text-blue-600 hover:underline">Kelola →</a>
                </div>
                @if ($this->lowStock->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">Semua stok aman 👍</p>
                @else
                    <ul class="flex flex-col gap-2">
                        @foreach ($this->lowStock as $v)
                            <li class="flex items-center gap-2 text-sm">
                                <span>{{ $v->product->emoji ?? '📦' }}</span>
                                <span class="min-w-0 flex-1 truncate">{{ $v->product->name }}{{ $v->label ? ' · '.$v->label : '' }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $v->stock === 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $v->stock }} {{ $v->product->unit }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Terlaris hari ini --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-sm font-bold">Terlaris Hari Ini</h2>
                    <a href="/laporan" class="ml-auto text-xs font-semibold text-blue-600 hover:underline">Laporan →</a>
                </div>
                @if ($this->topToday->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">Belum ada penjualan hari ini.</p>
                @else
                    <ul class="flex flex-col gap-2">
                        @foreach ($this->topToday as $i => $p)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="grid h-6 w-6 flex-none place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">{{ $i + 1 }}</span>
                                <span class="min-w-0 flex-1 truncate font-medium">{{ $p->name_snapshot }}</span>
                                <span class="text-xs font-semibold tabular-nums text-slate-400">{{ $p->qty }} terjual</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Transaksi terakhir --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-3 flex items-center gap-2">
                <h2 class="text-sm font-bold">Transaksi Terakhir</h2>
                <a href="/transaksi" class="ml-auto text-xs font-semibold text-blue-600 hover:underline">Semua →</a>
            </div>
            @if ($this->recent->isEmpty())
                <p class="py-6 text-center text-sm text-slate-400">Belum ada transaksi.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($this->recent as $tr)
                        <li class="flex items-center gap-3 py-2.5 text-sm">
                            <span class="font-semibold tabular-nums">{{ $tr->code }}</span>
                            <span class="text-xs text-slate-400">{{ $tr->created_at->translatedFormat('d M, H.i') }}</span>
                            <span class="text-xs text-slate-400">{{ $tr->member->name ?? 'Umum' }}</span>
                            <span class="ml-auto font-semibold tabular-nums">{{ rp($tr->total) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
