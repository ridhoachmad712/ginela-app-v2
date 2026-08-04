@php
    $typeMeta = [
        'PURCHASE' => ['Masuk', 'bg-green-100 text-green-700'],
        'SALE' => ['Penjualan', 'bg-red-100 text-red-700'],
        'ADJUSTMENT' => ['Penyesuaian', 'bg-amber-100 text-amber-700'],
        'INITIAL' => ['Awal', 'bg-accent-100 text-accent-700'],
    ];
    $vname = fn ($v) => $v->product->name . ($v->label ? ' · ' . $v->label : '');
@endphp

<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Manajemen Stok</h1>
            <p class="text-sm text-ink-soft">Pantau &amp; sesuaikan stok tiap varian</p>
        </div>

        {{-- Stok menipis --}}
        @if ($this->lowStock->isNotEmpty())
            <section class="rounded-2xl border border-amber-300 bg-amber-50 p-4">
                <h2 class="mb-3 text-sm font-bold text-amber-700">⚠ Stok Menipis ({{ $this->lowStock->count() }})</h2>
                <div class="flex flex-col gap-2">
                    @foreach ($this->lowStock as $v)
                        <div wire:key="low-{{ $v->id }}" class="flex items-center gap-3 rounded-xl bg-surface px-3 py-2 text-sm">
                            <span class="text-lg">{{ $v->product->emoji ?? '📦' }}</span>
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $vname($v) }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $v->stock === 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $v->stock }} {{ $v->product->unit }}</span>
                            <button wire:click="openAdd({{ $v->id }})" class="rounded-lg bg-accent-600 px-3 py-1.5 text-xs font-semibold text-white">+ Tambah</button>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Semua varian --}}
        <section class="flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-sm font-bold">Semua Varian</h2>
                <div class="ml-auto flex w-full max-w-xs items-center gap-2 rounded-xl border border-line bg-surface px-3">
                    <svg class="h-4 w-4 flex-none text-ink-faint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                    <input wire:model.live.debounce.300ms="q" placeholder="Cari produk / varian…" class="h-10 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0">
                </div>
            </div>
            <div class="overflow-x-auto card">
                <table class="w-full min-w-[560px] border-collapse text-sm">
                    <thead><tr class="border-b border-line bg-surface-2 text-left text-xs uppercase tracking-wide text-ink-soft">
                        <th class="px-4 py-3 font-semibold">Produk / Varian</th><th class="px-4 py-3 text-right font-semibold">Stok</th><th class="px-4 py-3 text-right font-semibold">Min</th><th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($this->variants as $v)
                            <tr wire:key="var-{{ $v->id }}" class="border-b border-line-soft last:border-0 hover:bg-surface-2">
                                <td class="px-4 py-3"><div class="flex items-center gap-3"><span class="text-lg">{{ $v->product->emoji ?? '📦' }}</span><span class="font-medium">{{ $vname($v) }}</span></div></td>
                                <td class="px-4 py-3 text-right"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $v->stock === 0 ? 'bg-red-100 text-red-700' : ($v->stock <= $v->min_stock ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $v->stock }} {{ $v->product->unit }}</span></td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-faint">{{ $v->min_stock }}</td>
                                <td class="px-4 py-3"><div class="flex justify-end gap-1.5">
                                    <button wire:click="openAdd({{ $v->id }})" class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-semibold hover:bg-surface-3">Tambah</button>
                                    <button wire:click="openSet({{ $v->id }})" class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-semibold hover:bg-surface-3">Opname</button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Riwayat pergerakan --}}
        <section class="flex flex-col gap-3">
            <h2 class="text-sm font-bold">Riwayat Pergerakan Stok</h2>
            <div class="overflow-x-auto card">
                <table class="w-full min-w-[640px] border-collapse text-sm">
                    <thead><tr class="border-b border-line bg-surface-2 text-left text-xs uppercase tracking-wide text-ink-soft">
                        <th class="px-4 py-3 font-semibold">Waktu</th><th class="px-4 py-3 font-semibold">Produk / Varian</th><th class="px-4 py-3 font-semibold">Tipe</th><th class="px-4 py-3 text-right font-semibold">Perubahan</th><th class="px-4 py-3 text-right font-semibold">Stok</th><th class="px-4 py-3 font-semibold">Ket.</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($this->movements as $m)
                            @php [$lab, $cls] = $typeMeta[$m->type] ?? [$m->type, 'bg-surface-3 text-ink-soft']; @endphp
                            <tr wire:key="mv-{{ $m->id }}" class="border-b border-line-soft last:border-0">
                                <td class="whitespace-nowrap px-4 py-3 text-ink-faint">{{ $m->created_at->translatedFormat('d M, H.i') }}</td>
                                <td class="px-4 py-3">{{ $m->variant ? $vname($m->variant) : '—' }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $cls }}">{{ $lab }}</span></td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums {{ $m->qty >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $m->qty >= 0 ? '+' : '' }}{{ $m->qty }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-faint">{{ $m->stock_before }} → {{ $m->stock_after }}</td>
                                <td class="px-4 py-3 text-ink-faint">{{ $m->ref_code ?? $m->note ?? '—' }}</td>
                            </tr>
                        @endforeach
                        @if ($this->movements->isEmpty())
                            <tr><td colspan="6" class="px-4 py-12 text-center text-ink-faint">Belum ada pergerakan stok.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Modal tambah/opname --}}
    @if ($this->modalVariant)
        @php $mv = $this->modalVariant; $isAdd = $modalMode === 'add'; $preview = $isAdd ? $mv->stock + (int) $qtyInput : (int) $qtyInput; @endphp
        <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>
            <div class="relative w-full rounded-t-3xl bg-surface p-5 sm:max-w-sm sm:rounded-3xl">
                <div class="mb-1 flex items-center gap-2"><h3 class="text-base font-bold">{{ $isAdd ? 'Tambah Stok' : 'Opname / Penyesuaian' }}</h3><button wire:click="close" class="ml-auto text-ink-faint">✕</button></div>
                <p class="mb-4 text-sm text-ink-soft">{{ $vname($mv) }} · stok kini <b class="text-ink">{{ $mv->stock }}</b> {{ $mv->product->unit }}</p>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">{{ $isAdd ? 'Jumlah masuk' : 'Stok fisik aktual' }} ({{ $mv->product->unit }})</span>
                    <input wire:model="qtyInput" inputmode="numeric" placeholder="0" class="h-11 rounded-xl border border-line px-3 text-sm"></label>
                <label class="mt-3 flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Catatan (opsional)</span>
                    <input wire:model="noteInput" placeholder="{{ $isAdd ? 'mis. Kiriman supplier' : 'mis. Barang rusak / selisih' }}" class="h-11 rounded-xl border border-line px-3 text-sm"></label>
                @if ($qtyInput !== '')
                    <p class="mt-3 rounded-lg bg-surface-3 px-3 py-2 text-sm text-ink-soft">Stok menjadi <b class="text-ink tabular-nums">{{ $preview }}</b> {{ $mv->product->unit }}</p>
                @endif
                @if ($error)<p class="mt-2 text-sm text-red-600">{{ $error }}</p>@endif
                <div class="mt-5 flex gap-3">
                    <button wire:click="close" class="h-12 flex-1 rounded-xl border border-line font-semibold">Batal</button>
                    <button wire:click="save" class="h-12 flex-1 rounded-xl bg-accent-600 font-semibold text-white">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
