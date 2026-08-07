<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex flex-col gap-3 border-b border-line bg-surface px-5 pb-3 pt-5">
        {{-- Breadcrumb --}}
        <div class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm">
            <button wire:click="goRoot" class="font-bold tracking-tight {{ $selCat === null ? 'text-ink' : 'text-accent-600 hover:underline' }}">Produk</button>
            @if ($selCat === 'all')
                <span class="text-ink-faint">/</span><span class="font-semibold">Semua Produk</span>
            @elseif ($selCat === 'none')
                <span class="text-ink-faint">/</span><span class="font-semibold">Tanpa Kategori</span>
            @endif
            @foreach ($this->crumbs as $bc)
                <span class="text-ink-faint">/</span>
                @if ($loop->last)
                    <span class="font-semibold">{{ $bc->name }}</span>
                @else
                    <button wire:click="openCat('{{ $bc->id }}', @js($bc->name))" class="font-semibold text-accent-600 hover:underline">{{ $bc->name }}</button>
                @endif
            @endforeach

            @if ($isAdmin)
                <div class="ml-auto flex items-center gap-2">
                    <button wire:click="openCatModal" class="flex items-center gap-1.5 rounded-xl border border-line px-4 py-2 text-sm font-semibold text-ink-soft transition hover:bg-surface-3 hover:text-ink">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h4l2 2h11a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><circle cx="12" cy="14" r="2.5"/></svg>
                        Kelola Kategori
                    </button>
                    <button wire:click="openNew" class="rounded-xl bg-accent-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-accent-700">+ Tambah Produk</button>
                </div>
            @endif
        </div>
        @if ($selCat !== null)
            <div class="flex max-w-md items-center gap-2 rounded-xl border border-line bg-surface px-3">
                <svg class="h-4 w-4 flex-none text-ink-faint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input wire:model.live.debounce.300ms="q" placeholder="Cari produk di sini…" class="h-11 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0">
            </div>
        @endif
    </div>

    <div class="min-h-0 flex-1 overflow-auto px-5 py-4 pb-24 md:pb-4">
        {{-- Ringkasan inventaris (akar) --}}
        @if ($selCat === null)
            @php $st = $this->stats; @endphp
            <div class="mb-4 grid grid-cols-3 gap-3">
                <div class="card p-4"><div class="text-xs font-semibold text-ink-soft">Produk aktif</div><div class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl">{{ number_format($st['products']) }}</div></div>
                <div class="card p-4"><div class="text-xs font-semibold text-ink-soft">Nilai inventaris</div><div class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl">{{ rp($st['invValue']) }}</div></div>
                <div class="card p-4"><div class="text-xs font-semibold text-ink-soft">Perlu restock</div><div class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl {{ $st['lowStock'] > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ number_format($st['lowStock']) }}</div></div>
            </div>
        @endif

        {{-- Kartu kategori / sub-kategori --}}
        @if (count($this->catCards))
            @if ($selCat !== null)<div class="mb-2 text-[10px] font-bold uppercase tracking-wider text-ink-faint">Sub-kategori</div>@endif
            <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($this->catCards as $c)
                    <button wire:key="cat-{{ $c['id'] }}" wire:click="openCat('{{ $c['id'] }}', @js($c['name']))"
                            class="flex flex-col items-start gap-2 card p-4 text-left transition hover:border-accent-600 hover:shadow-sm">
                        <div class="flex w-full items-start">
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-surface-3 text-2xl">{{ $c['emoji'] }}</span>
                            @if (($c['kids'] ?? 0) > 0)<span class="ml-auto rounded-full bg-surface-3 px-2 py-0.5 text-[10px] font-bold text-ink-soft">{{ $c['kids'] }} sub</span>@endif
                        </div>
                        <span class="font-semibold leading-tight">{{ $c['name'] }}</span>
                        <span class="text-xs text-ink-faint">{{ $c['count'] }} produk</span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Tabel produk (di dalam node / semua / tanpa kategori) --}}
        @if ($selCat !== null)
            <div class="mb-2 flex items-center gap-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-ink-faint">Produk</div>
                <span class="text-xs text-ink-faint">{{ $this->products->count() }} item</span>
            </div>
            <div class="overflow-x-auto card">
                <table class="w-full min-w-[720px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2 text-left text-xs uppercase tracking-wide text-ink-soft">
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
                            <tr wire:key="row-{{ $p->id }}" class="border-b border-line-soft last:border-0 hover:bg-surface-2">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 flex-none place-items-center overflow-hidden rounded-lg bg-surface-3 text-lg">
                                            @if ($p->image_path)<img src="{{ asset('storage/' . $p->image_path) }}" class="h-full w-full object-cover">@else{{ $p->emoji ?? '📦' }}@endif
                                        </span>
                                        <div><a href="/produk/{{ $p->id }}" wire:navigate class="font-semibold transition hover:text-accent-600 hover:underline">{{ $p->name }}</a><div class="text-xs text-ink-faint">{{ $p->category->name ?? '—' }} · per {{ $p->unit }}</div></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-ink-soft">{{ $p->attributes->count() ? $p->variants->count().' varian' : 'Tunggal' }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $offMin === $offMax ? rp($offMin) : rp($offMin).'–'.number_format($offMax,0,',','.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-soft">{{ $onMin === $onMax ? rp($onMin) : rp($onMin).'–'.number_format($onMax,0,',','.') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $stock === 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : ($low ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-400') }}">{{ $stock }} {{ $p->unit }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="/produk/{{ $p->id }}" wire:navigate class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-semibold hover:bg-surface-3">Detail</a>
                                        <button wire:click="openEdit({{ $p->id }})" @disabled(!$isAdmin) class="rounded-lg border border-line px-2.5 py-1.5 text-xs font-semibold hover:bg-surface-3 disabled:opacity-40">Edit</button>
                                        <button wire:click="$set('deletingId', {{ $p->id }})" @disabled(!$isAdmin) class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-40 dark:border-red-500/30 dark:hover:bg-red-500/15">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($this->products->isEmpty())
                            <tr><td colspan="6" class="px-4 py-12 text-center text-ink-faint">{{ count($this->catCards) ? 'Tidak ada produk langsung di kategori ini — pilih sub-kategori di atas.' : 'Tidak ada produk.' }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============ Modal Create / Edit ============ --}}
    @if ($mode)
        <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>
            <div class="relative flex max-h-[92vh] w-full flex-col rounded-t-3xl bg-surface sm:max-w-2xl sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-line px-5 py-4">
                    <h3 class="text-base font-bold">{{ $mode === 'new' ? 'Tambah Produk' : 'Edit Produk' }}</h3>
                    <button wire:click="close" class="ml-auto text-ink-faint hover:text-ink">✕</button>
                </div>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="col-span-2 flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Nama produk</span>
                            <input wire:model="fName" class="h-11 rounded-xl border border-line px-3 text-sm" placeholder="mis. Hijab Basic Hitam"></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Kategori</span>
                            <select wire:model="fCategory" class="h-11 rounded-xl border border-line px-3 text-sm">
                                <option value="">— Tanpa kategori —</option>
                                @foreach ($this->orderedCats as $n)<option value="{{ $n['cat']->id }}">{{ str_repeat('— ', $n['depth']) }}{{ $n['cat']->name }}</option>@endforeach
                            </select></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Satuan</span>
                            <input wire:model="fUnit" class="h-11 rounded-xl border border-line px-3 text-sm" placeholder="pcs"></label>
                        <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Emoji (cadangan bila tanpa foto)</span>
                            <input wire:model="fEmoji" class="h-11 rounded-xl border border-line px-3 text-sm" placeholder="🧕"></label>
                    </div>

                    {{-- Foto produk --}}
                    <div class="flex items-center gap-4 rounded-xl border border-line bg-surface-2 p-3">
                        <div class="grid h-16 w-16 flex-none place-items-center overflow-hidden rounded-xl bg-surface">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="h-full w-full object-cover">
                            @elseif ($existingImage)
                                <img src="{{ asset('storage/' . $existingImage) }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl">{{ $fEmoji ?: '📦' }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-semibold text-ink-soft">Foto produk (opsional, maks 2MB)</div>
                            <input type="file" wire:model="photo" accept="image/*" class="mt-1 block w-full text-sm text-ink-soft file:mr-3 file:rounded-lg file:border-0 file:bg-accent-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                            <div wire:loading wire:target="photo" class="mt-1 text-xs text-accent-600">Mengunggah…</div>
                            @error('photo')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Varian (tersedia di Tambah & Edit) --}}
                    <div class="flex flex-col gap-3 rounded-xl border border-line bg-surface-2 p-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-ink-soft">Varian (opsional)</span>
                                <button wire:click="addAttr" class="ml-auto text-xs font-semibold text-accent-600 hover:underline">+ Tambah varian</button>
                            </div>
                            @forelse ($attrs as $i => $a)
                                <div wire:key="attr-{{ $i }}" class="flex flex-col gap-2 rounded-lg border border-line bg-surface p-2.5">
                                    <div class="flex items-center gap-2">
                                        <input wire:model="attrs.{{ $i }}.name" placeholder="Nama varian (mis. Warna)" class="h-9 flex-1 rounded-lg border border-line px-2 text-sm font-semibold">
                                        <button wire:click="removeAttr({{ $i }})" class="grid h-8 w-8 flex-none place-items-center rounded-lg text-ink-faint transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15" aria-label="Hapus varian">✕</button>
                                    </div>
                                    <div class="flex flex-col gap-1.5 pl-1">
                                        <span class="text-[11px] font-semibold text-ink-faint">Opsi (satu per baris):</span>
                                        @foreach ($a['options'] as $j => $opt)
                                            <div wire:key="opt-{{ $i }}-{{ $j }}" class="flex items-center gap-2">
                                                <span class="text-ink-faint">•</span>
                                                <input wire:model="attrs.{{ $i }}.options.{{ $j }}" wire:keydown.enter="addOpt({{ $i }})" placeholder="mis. Hitam" class="h-8 flex-1 rounded-lg border border-line px-2 text-sm">
                                                <button wire:click="removeOpt({{ $i }}, {{ $j }})" class="grid h-7 w-7 flex-none place-items-center rounded-lg text-ink-faint transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15" aria-label="Hapus opsi">✕</button>
                                            </div>
                                        @endforeach
                                        <button wire:click="addOpt({{ $i }})" class="self-start rounded-lg border border-line px-2 py-1 text-xs font-semibold text-accent-600 hover:bg-surface-3">+ tambah opsi</button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-ink-faint">Tanpa varian = produk harga tunggal. Tambah varian (mis. <b>Warna</b>) lalu isi opsinya (Hitam, Navy…) satu per satu.</p>
                            @endforelse
                            @if (count($attrs))
                                <button wire:click="generateVariants" class="self-start rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-semibold hover:bg-surface-3">Buat / segarkan varian</button>
                            @endif
                    </div>

                    @if ($mode === 'new')
                        <p class="text-xs text-ink-faint">Isi harga tiap varian: <b>Modal</b>, <b>Offline</b>, &amp; <b>Online</b>.</p>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[520px] text-sm">
                                <thead><tr class="text-left text-xs uppercase tracking-wide text-ink-soft">
                                    <th class="py-2 pr-2 font-semibold">Varian</th><th class="px-1 py-2 font-semibold">Modal</th><th class="px-1 py-2 font-semibold">Harga offline</th><th class="px-1 py-2 font-semibold">Harga online</th><th class="px-1 py-2 font-semibold">Stok</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($rows as $i => $r)
                                        <tr wire:key="nr-{{ $i }}">
                                            <td class="py-1 pr-2 font-medium">{{ $r['label'] ?: 'Tunggal' }}</td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.cost" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.offline" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.online" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                            <td class="px-1 py-1"><input wire:model="rows.{{ $i }}.stock" inputmode="numeric" class="h-9 w-20 rounded-lg border border-line px-2 text-right text-sm tabular-nums" placeholder="0"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-ink-faint">Menambah warna? Isi <b>Varian</b> di atas → <b>Buat / segarkan varian</b>, lalu isi Modal/Offline/Online/Stok tiap warna. Varian lama yang sudah ada transaksinya akan dinonaktifkan (tidak dihapus).</p>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px] text-sm">
                                <thead><tr class="text-left text-xs uppercase tracking-wide text-ink-soft">
                                    <th class="py-2 pr-2 font-semibold">Varian</th><th class="px-1 py-2 font-semibold">Modal</th><th class="px-1 py-2 font-semibold">Harga offline</th><th class="px-1 py-2 font-semibold">Harga online</th><th class="px-1 py-2 font-semibold">Stok</th><th class="px-1 py-2 font-semibold">Aktif</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($editRows as $i => $r)
                                        <tr wire:key="er-{{ $r['id'] }}">
                                            <td class="py-1 pr-2 font-medium">{{ $r['label'] ?: 'Tunggal' }}</td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.cost" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.offline" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.online" inputmode="numeric" class="h-9 w-24 rounded-lg border border-line px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1"><input wire:model="editRows.{{ $i }}.stock" inputmode="numeric" class="h-9 w-20 rounded-lg border border-line px-2 text-right text-sm tabular-nums"></td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox" wire:model="editRows.{{ $i }}.active"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($error)<div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700">{{ $error }}</div>@endif
                </div>

                <div class="flex gap-3 border-t border-line px-5 py-4">
                    <button wire:click="close" class="h-12 flex-1 rounded-xl border border-line font-semibold">Batal</button>
                    <button wire:click="{{ $mode === 'new' ? 'saveNew' : 'saveEdit' }}" class="h-12 flex-1 rounded-xl bg-accent-600 font-semibold text-white hover:bg-accent-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ Konfirmasi hapus ============ --}}
    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('deletingId', null)"></div>
            <div class="relative w-full max-w-sm rounded-3xl bg-surface p-6 text-center shadow-pop">
                <h3 class="text-lg font-bold">Hapus produk?</h3>
                <p class="mt-1 text-sm text-ink-soft">Produk dinonaktifkan dari katalog. Riwayat penjualan tetap tersimpan.</p>
                <div class="mt-5 flex gap-3">
                    <button wire:click="$set('deletingId', null)" class="h-12 flex-1 rounded-xl border border-line font-semibold">Batal</button>
                    <button wire:click="deleteProduct" class="h-12 flex-1 rounded-xl bg-red-600 font-semibold text-white">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ Modal Kelola Kategori (bertingkat) ============ --}}
    @if ($catModal)
        <div class="fixed inset-0 z-[55] flex items-end justify-center bg-black/40 sm:items-center sm:p-4" wire:key="cat-modal">
            <div class="flex max-h-[88vh] w-full max-w-lg flex-col rounded-t-3xl bg-surface shadow-pop sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-line px-5 py-4">
                    <h2 class="text-lg font-bold tracking-tight">Kelola Kategori</h2>
                    <button wire:click="closeCatModal" class="ml-auto grid h-9 w-9 place-items-center rounded-lg text-ink-faint transition hover:bg-surface-3 hover:text-ink" aria-label="Tutup">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tambah kategori utama (cukup nama) --}}
                <div class="flex items-center gap-2 border-b border-line px-5 py-3">
                    <input wire:model="quickAddName" wire:keydown.enter="addTop" placeholder="Tambah kategori utama…"
                           class="h-11 flex-1 rounded-xl border border-line bg-surface px-3 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-accent-100">
                    <button wire:click="addTop" class="h-11 flex-none rounded-xl bg-accent-600 px-4 text-sm font-bold text-white transition hover:bg-accent-700">+ Tambah</button>
                </div>
                @if ($catError && ! $catEditId && ! $subAddUnder)<p class="px-5 pt-2 text-xs font-medium text-red-600">{{ $catError }}</p>@endif

                {{-- Pohon kategori --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-3 py-2">
                    @forelse ($this->orderedCats as $n)
                        @php $c = $n['cat']; $inh = $c->feeIsInherited(); $eff = $c->shopeeFeeRate(); @endphp
                        <div wire:key="catm-{{ $c->id }}">
                            @if ($catEditId === $c->id)
                                {{-- Edit inline --}}
                                <div class="my-1 flex flex-col gap-2 rounded-xl border-2 border-accent-500 bg-surface-2 p-3" style="margin-left: {{ $n['depth'] * 1 }}rem">
                                    <input wire:model="catName" wire:keydown.enter="saveCat" placeholder="Nama kategori" class="h-10 rounded-lg border border-line bg-surface px-3 text-sm">
                                    <label class="flex items-center gap-2 text-xs text-ink-soft">Induk
                                        <select wire:model="catParent" class="h-9 flex-1 rounded-lg border border-line bg-surface px-2 text-xs">
                                            <option value="">— Kategori utama —</option>
                                            @foreach ($this->orderedCats as $m)
                                                <option value="{{ $m['cat']->id }}" @disabled($m['cat']->id === $c->id)>{{ str_repeat('— ', $m['depth']).$m['cat']->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <details><summary class="cursor-pointer text-xs text-ink-soft">Fee Shopee (opsional — kosong = ikut induk)</summary>
                                        <div class="mt-1 flex gap-2">
                                            <input wire:model="catAdmin" inputmode="decimal" placeholder="admin %" class="h-9 flex-1 rounded-lg border border-line bg-surface px-2 text-xs">
                                            <input wire:model="catService" inputmode="decimal" placeholder="layanan %" class="h-9 flex-1 rounded-lg border border-line bg-surface px-2 text-xs">
                                        </div>
                                    </details>
                                    @if ($catError)<p class="text-xs font-medium text-red-600">{{ $catError }}</p>@endif
                                    <div class="flex gap-2">
                                        <button wire:click="saveCat" class="h-9 flex-1 rounded-lg bg-accent-600 text-xs font-bold text-white">Simpan</button>
                                        <button wire:click="resetCatForm" class="h-9 rounded-lg border border-line px-3 text-xs font-semibold text-ink-soft">Batal</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 rounded-xl px-2 py-2 transition hover:bg-surface-2" style="padding-left: {{ 0.5 + $n['depth'] * 1.25 }}rem">
                                    <span class="text-ink-faint">{{ $n['depth'] > 0 ? '└' : '🏷️' }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-semibold">{{ $c->name }}</div>
                                        <div class="text-[11px] text-ink-faint">{{ $c->products_count }} produk @if ($eff > 0)· fee {{ rtrim(rtrim(number_format($eff * 100, 2), '0'), '.') }}%{{ $inh ? ' (warisan)' : '' }}@endif</div>
                                    </div>
                                    @if ($catDeleting === $c->id)
                                        <span class="text-xs font-medium text-ink-soft">Hapus?</span>
                                        <button wire:click="deleteCat" class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white">Ya</button>
                                        <button wire:click="cancelDeleteCat" class="rounded-lg border border-line px-2.5 py-1 text-xs font-semibold text-ink-soft">Batal</button>
                                    @else
                                        <button wire:click="startSub({{ $c->id }})" class="rounded-lg border border-line px-2 py-1 text-[11px] font-semibold text-ink-soft transition hover:bg-surface-3 hover:text-accent-600">+ Sub</button>
                                        <button wire:click="editCat({{ $c->id }})" class="grid h-7 w-7 flex-none place-items-center rounded-lg text-ink-faint transition hover:bg-surface-3 hover:text-accent-600" aria-label="Edit">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                                        </button>
                                        <button wire:click="askDeleteCat({{ $c->id }})" class="grid h-7 w-7 flex-none place-items-center rounded-lg text-ink-faint transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15" aria-label="Hapus">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
                                        </button>
                                    @endif
                                </div>
                                @if ($subAddUnder === $c->id)
                                    <div class="flex items-center gap-2 py-1" style="padding-left: {{ 1.75 + $n['depth'] * 1.25 }}rem">
                                        <input wire:model="subAddName" wire:keydown.enter="saveSub" placeholder="Nama sub-kategori di {{ $c->name }}…" class="h-9 flex-1 rounded-lg border-2 border-accent-500 bg-surface px-2 text-sm">
                                        <button wire:click="saveSub" class="h-9 rounded-lg bg-accent-600 px-3 text-xs font-bold text-white">Simpan</button>
                                        <button wire:click="cancelSub" class="h-9 rounded-lg border border-line px-2 text-xs text-ink-soft">✕</button>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-ink-faint">Belum ada kategori. Tambahkan di atas.</p>
                    @endforelse
                </div>

                <div class="border-t border-line px-5 py-3">
                    <p class="text-xs text-ink-faint"><b>+ Sub</b> tambah sub-kategori di dalamnya · <b>✎</b> ubah nama/induk/fee · hapus → produk & sub jadi tanpa induk.</p>
                </div>
            </div>
        </div>
    @endif
</div>
