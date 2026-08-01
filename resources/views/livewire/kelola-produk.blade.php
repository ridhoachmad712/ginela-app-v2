<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 pb-3 pt-5">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold tracking-tight">Kelola Produk</h1>
            <span class="text-sm text-slate-400">{{ $this->products->count() }} produk</span>
            @if ($isAdmin)
                <button wire:click="openNew" class="ml-auto rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Produk</button>
            @endif
        </div>
        <div class="flex max-w-md items-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
            <svg class="h-4 w-4 flex-none text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
            <input wire:model.live.debounce.300ms="q" placeholder="Cari produk…" class="h-11 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0">
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-auto px-5 py-4 pb-24 md:pb-4">
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full min-w-[720px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Produk</th>
                        <th class="px-4 py-3 font-semibold">Varian</th>
                        <th class="px-4 py-3 text-right font-semibold">Offline</th>
                        <th class="px-4 py-3 text-right font-semibold">Online</th>
                        <th class="px-4 py-3 text-right font-semibold">Stok</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->products as $p)
                        @php
                            $stock = $p->variants->sum('stock');
                            $low = $p->variants->contains(fn ($v) => $v->stock <= $v->min_stock);
                            $offMin = $p->variants->min('offline_price'); $offMax = $p->variants->max('offline_price');
                            $onMin = $p->variants->min('online_price'); $onMax = $p->variants->max('online_price');
                        @endphp
                        <tr wire:key="row-{{ $p->id }}" class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 flex-none place-items-center rounded-lg bg-slate-100 text-lg">{{ $p->emoji ?? '📦' }}</span>
                                    <div><div class="font-semibold">{{ $p->name }}</div><div class="text-xs text-slate-400">{{ $p->category->name ?? '—' }} · per {{ $p->unit }}</div></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->attributes->count() ? $p->variants->count().' varian' : 'Tunggal' }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $offMin === $offMax ? rp($offMin) : rp($offMin).'–'.number_format($offMax,0,',','.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $onMin === $onMax ? rp($onMin) : rp($onMin).'–'.number_format($onMax,0,',','.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $stock === 0 ? 'bg-red-100 text-red-700' : ($low ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $stock }} {{ $p->unit }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <button wire:click="openEdit({{ $p->id }})" @disabled(!$isAdmin) class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold hover:bg-slate-100 disabled:opacity-40">Edit</button>
                                    <button wire:click="$set('deletingId', {{ $p->id }})" @disabled(!$isAdmin) class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-40">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($this->products->isEmpty())
                        <tr><td colspan="6" class="px-4 py-14 text-center text-slate-400">Tidak ada produk.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ Modal Create / Edit ============ --}}
    @if ($mode)
        <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>
            <div class="relative flex max-h-[92vh] w-full flex-col rounded-t-3xl bg-white sm:max-w-2xl sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-slate-200 px-5 py-4">
                    <h3 class="text-base font-bold">{{ $mode === 'new' ? 'Tambah Produk' : 'Edit Produk' }}</h3>
                    <button wire:click="close" class="ml-auto text-slate-400 hover:text-slate-700">✕</button>
                </div>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="col-span-2 flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Nama produk</span>
                            <input wire:model="fName" class="h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="mis. Kaos Polos"></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Kategori</span>
                            <select wire:model="fCategory" class="h-11 rounded-xl border border-slate-200 px-3 text-sm">
                                <option value="">— Tanpa kategori —</option>
                                @foreach ($this->categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Satuan</span>
                            <input wire:model="fUnit" class="h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="pcs"></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Emoji</span>
                            <input wire:model="fEmoji" class="h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="👕"></label>
                    </div>

                    @if ($mode === 'new')
                        {{-- Atribut --}}
                        <div class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Atribut (opsional)</span>
                                <button wire:click="addAttr" class="ml-auto text-xs font-semibold text-blue-600 hover:underline">+ Tambah atribut</button>
                            </div>
                            @forelse ($attrs as $i => $a)
                                <div wire:key="attr-{{ $i }}" class="flex items-center gap-2">
                                    <input wire:model="attrs.{{ $i }}.name" placeholder="Nama (mis. Ukuran)" class="h-9 w-36 rounded-lg border border-slate-200 px-2 text-sm">
                                    <input wire:model="attrs.{{ $i }}.opts" placeholder="Opsi, pisah koma: S, M, L" class="h-9 flex-1 rounded-lg border border-slate-200 px-2 text-sm">
                                    <button wire:click="removeAttr({{ $i }})" class="text-slate-400 hover:text-red-600">✕</button>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Tanpa atribut = produk harga tunggal. Tambah atribut untuk banyak varian.</p>
                            @endforelse
                            <button wire:click="generateVariants" class="self-start rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold hover:bg-white">Buat / segarkan varian</button>
                        </div>

                        {{-- Matriks varian --}}
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[500px] text-sm">
                                <thead><tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="py-2 pr-2 font-semibold">Varian</th><th class="px-1 py-2 font-semibold">Offline</th><th class="px-1 py-2 font-semibold">Online</th><th class="px-1 py-2 font-semibold">HPP</th><th class="px-1 py-2 font-semibold">Stok</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($rows as $i => $r)
                                        <tr wire:key="nr-{{ $i }}">
                                            <td class="py-1 pr-2 font-medium">{{ $r['label'] ?: 'Tunggal' }}</td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.offline" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.online" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.cost" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.stock" inputmode="numeric" class="h-9 w-20 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- Edit: varian per baris --}}
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px] text-sm">
                                <thead><tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="py-2 pr-2 font-semibold">Varian</th><th class="px-1 py-2 font-semibold">Offline</th><th class="px-1 py-2 font-semibold">Online</th><th class="px-1 py-2 font-semibold">HPP</th><th class="px-1 py-2 font-semibold">Stok</th><th class="px-1 py-2 font-semibold">Aktif</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($editRows as $i => $r)
                                        <tr wire:key="er-{{ $r['id'] }}">
                                            <td class="py-1 pr-2 font-medium">{{ $r['label'] ?: 'Tunggal' }}</td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.offline" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.online" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.cost" inputmode="numeric" class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.stock" inputmode="numeric" class="h-9 w-20 rounded-lg border border-slate-200 px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox" wire:model="editRows.{{ $i }}.active"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($error)<div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700">{{ $error }}</div>@endif
                </div>

                <div class="flex gap-3 border-t border-slate-200 px-5 py-4">
                    <button wire:click="close" class="h-12 flex-1 rounded-xl border border-slate-300 font-semibold">Batal</button>
                    <button wire:click="{{ $mode === 'new' ? 'saveNew' : 'saveEdit' }}" class="h-12 flex-1 rounded-xl bg-blue-600 font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ Konfirmasi hapus ============ --}}
    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('deletingId', null)"></div>
            <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center">
                <h3 class="text-lg font-bold">Hapus produk?</h3>
                <p class="mt-1 text-sm text-slate-500">Produk dinonaktifkan dari katalog. Riwayat penjualan tetap tersimpan.</p>
                <div class="mt-5 flex gap-3">
                    <button wire:click="$set('deletingId', null)" class="h-12 flex-1 rounded-xl border border-slate-300 font-semibold">Batal</button>
                    <button wire:click="deleteProduct" class="h-12 flex-1 rounded-xl bg-red-600 font-semibold text-white">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
