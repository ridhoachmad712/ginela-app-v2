<div class="flex min-h-0 flex-1">
    {{-- ================= Katalog ================= --}}
    <section class="flex min-w-0 flex-1 flex-col">
        <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 pb-3 pt-5">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight">Katalog Produk</h1>
                <span class="ml-auto rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Harga: {{ $channel === 'ONLINE' ? 'Online' : 'Offline' }}</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3">
                <svg class="h-4 w-4 flex-none text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input wire:model.live.debounce.300ms="q" type="text" placeholder="Cari produk…"
                       class="h-11 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0" />
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($this->categories as $c)
                    <button wire:click="$set('cat','{{ $c }}')"
                            class="whitespace-nowrap rounded-full border px-4 py-1.5 text-sm font-semibold transition {{ $cat === $c ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 bg-white text-slate-500' }}">
                        {{ $c }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 pb-40 md:pb-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->filtered as $p)
                    @php
                        $stock = $p->variants->sum('stock');
                        $hasVar = $p->attributes->count() > 0;
                        $min = $p->variants->min(fn ($v) => $channel === 'ONLINE' ? $v->online_price : $v->offline_price);
                        $out = $stock <= 0;
                    @endphp
                    <div wire:key="p-{{ $p->id }}" class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3">
                        <div class="flex items-start justify-between">
                            <span class="grid h-12 w-12 place-items-center overflow-hidden rounded-xl bg-slate-100 text-2xl">
                                @if ($p->image_path)<img src="{{ asset('storage/' . $p->image_path) }}" class="h-full w-full object-cover">@else{{ $p->emoji ?? '📦' }}@endif
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $out ? 'bg-red-100 text-red-700' : ($stock <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                {{ $out ? 'Habis' : ($stock <= 5 ? 'Sisa '.$stock : 'Stok '.$stock) }}
                            </span>
                        </div>
                        <p class="min-h-[2.5rem] text-sm font-semibold leading-tight">{{ $p->name }}</p>
                        <p class="text-base font-extrabold tabular-nums">
                            @if ($hasVar)<span class="mr-1 text-xs font-medium text-slate-400">mulai</span>@endif{{ rp($min) }}
                        </p>
                        <button wire:click="pick({{ $p->id }})" @disabled($out)
                                class="mt-auto flex h-9 items-center justify-center gap-1.5 rounded-xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400">
                            {{ $hasVar ? 'Pilih Varian' : 'Tambah' }}
                        </button>
                    </div>
                @endforeach
            </div>
            @if ($this->filtered->isEmpty())
                <p class="py-16 text-center text-sm text-slate-400">Produk tidak ditemukan.</p>
            @endif
        </div>
    </section>

    {{-- ================= Keranjang (tablet+) ================= --}}
    <aside class="hidden w-[360px] flex-none flex-col border-l border-slate-200 bg-white md:flex">
        @include('livewire.partials.cart')
    </aside>

    {{-- ================= Pemilih varian ================= --}}
    @if ($this->pickingProduct)
        @php $pp = $this->pickingProduct; @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('picking', null)"></div>
            <div class="relative flex max-h-[80vh] w-full flex-col rounded-t-3xl bg-white sm:max-w-md sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-slate-200 px-5 py-4">
                    <span class="text-xl">{{ $pp->emoji ?? '📦' }}</span>
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-bold">{{ $pp->name }}</h3>
                        <p class="text-xs text-slate-400">Pilih varian ({{ $pp->attributes->pluck('name')->join(' · ') }})</p>
                    </div>
                    <button wire:click="$set('picking', null)" class="ml-auto text-slate-400 hover:text-slate-700">✕</button>
                </div>
                <div class="grid min-h-0 flex-1 grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2">
                    @foreach ($pp->variants as $v)
                        @php $vout = $v->stock <= 0; $vp = $channel === 'ONLINE' ? $v->online_price : $v->offline_price; @endphp
                        <button wire:key="v-{{ $v->id }}" wire:click="add({{ $v->id }})" @disabled($vout)
                                class="flex flex-col items-start gap-1 rounded-xl border border-slate-200 bg-white p-3 text-left transition enabled:hover:border-blue-600 disabled:opacity-50">
                            <span class="text-sm font-semibold">{{ $v->label ?: 'Standar' }}</span>
                            <span class="text-base font-extrabold tabular-nums">{{ rp($vp) }}</span>
                            <span class="text-xs {{ $vout ? 'text-red-600' : 'text-slate-400' }}">{{ $vout ? 'Habis' : 'Stok '.$v->stock }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ================= Pembayaran / Sukses ================= --}}
    @include('livewire.partials.payment')
</div>
