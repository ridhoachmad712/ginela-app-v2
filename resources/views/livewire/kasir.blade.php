<div x-data="{ draft: false }" class="flex min-h-0 flex-1">
    {{-- ================= Katalog ================= --}}
    <section class="flex min-w-0 flex-1 flex-col">
        <div class="flex flex-col gap-3 border-b border-line bg-surface px-5 pb-3 pt-5">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight">Pilih Produk</h1>
                <span class="ml-auto rounded-lg bg-surface-3 px-2.5 py-1 text-xs font-semibold text-ink-soft">Harga: {{ $channel === 'ONLINE' ? 'Shopee' : 'Offline' }}</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-line bg-surface px-3">
                <svg class="h-4 w-4 flex-none text-ink-faint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input wire:model.live.debounce.300ms="q" type="text" placeholder="Cari produk…"
                       class="h-11 flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0" />
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($this->categories as $c)
                    <button wire:click="$set('cat','{{ $c }}')"
                            class="whitespace-nowrap rounded-full border px-4 py-1.5 text-sm font-semibold transition {{ $cat === $c ? 'border-accent-600 bg-accent-600 text-white' : 'border-line bg-surface text-ink-soft hover:border-accent-600' }}">
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
                    <div wire:key="p-{{ $p->id }}" class="flex animate-fade-in flex-col gap-2 card p-3 transition hover:-translate-y-0.5 hover:border-accent-600 hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <span class="grid h-12 w-12 place-items-center overflow-hidden rounded-xl bg-surface-3 text-2xl">
                                @if ($p->image_path)<img src="{{ asset('storage/' . $p->image_path) }}" class="h-full w-full object-cover">@else{{ $p->emoji ?? '📦' }}@endif
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $out ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : ($stock <= 5 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-400') }}">
                                {{ $out ? 'Habis' : ($stock <= 5 ? 'Sisa '.$stock : 'Stok '.$stock) }}
                            </span>
                        </div>
                        <p class="min-h-[2.5rem] text-sm font-semibold leading-tight">{{ $p->name }}</p>
                        <p class="text-base font-extrabold tabular-nums text-ink">
                            @if ($hasVar)<span class="mr-1 text-xs font-medium text-ink-faint">mulai</span>@endif{{ rp($min) }}
                        </p>
                        <button wire:click="pick({{ $p->id }})" @disabled($out)
                                class="mt-auto flex h-9 items-center justify-center gap-1.5 rounded-xl bg-accent-600 text-sm font-semibold text-white transition hover:bg-accent-700 disabled:bg-surface-3 disabled:text-ink-faint">
                            {{ $hasVar ? 'Pilih Varian' : '+ Tambah' }}
                        </button>
                    </div>
                @endforeach
            </div>
            @if ($this->filtered->isEmpty())
                <div class="flex animate-fade-in flex-col items-center justify-center gap-3 py-20 text-center text-ink-faint">
                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-surface-3">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                    </div>
                    <p class="text-sm font-medium">Tidak ada produk yang cocok.<br>Coba kata kunci atau kategori lain.</p>
                    @if ($q !== '' || $cat !== 'Semua')
                        <button wire:click="$set('q',''); $set('cat','Semua')" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-accent-600 hover:bg-accent-50 dark:hover:bg-accent-500/15">Reset pencarian</button>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- ================= Draft: sidebar (md+) / sheet (HP) ================= --}}
    <aside class="fixed inset-0 z-50 flex transform flex-col bg-surface transition-transform duration-300 md:static md:z-auto md:w-[380px] md:flex-none md:transform-none md:border-l md:border-line"
           :class="draft ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">
        <button type="button" @click="draft = false" class="flex items-center gap-2 border-b border-line px-4 py-2.5 text-sm font-semibold text-ink-soft md:hidden">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            Kembali ke katalog
        </button>
        @include('livewire.partials.cart')
    </aside>

    {{-- ================= Pemilih varian ================= --}}
    @if ($this->pickingProduct)
        @php $pp = $this->pickingProduct; @endphp
        <div class="fixed inset-0 z-[55] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 animate-fade-in bg-black/50" wire:click="$set('picking', null)"></div>
            <div class="relative flex max-h-[80vh] w-full animate-slide-up flex-col rounded-t-3xl bg-surface sm:max-w-md sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-line px-5 py-4">
                    <span class="text-xl">{{ $pp->emoji ?? '📦' }}</span>
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-bold">{{ $pp->name }}</h3>
                        <p class="text-xs text-ink-faint">Pilih varian ({{ $pp->attributes->pluck('name')->join(' · ') }})</p>
                    </div>
                    <button wire:click="$set('picking', null)" class="ml-auto text-ink-faint hover:text-ink">✕</button>
                </div>
                <div class="grid min-h-0 flex-1 grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2">
                    @foreach ($pp->variants as $v)
                        @php $vout = $v->stock <= 0; $vp = $channel === 'ONLINE' ? $v->online_price : $v->offline_price; @endphp
                        <button wire:key="v-{{ $v->id }}" wire:click="add({{ $v->id }})" @disabled($vout)
                                class="flex flex-col items-start gap-1 rounded-xl border border-line bg-surface p-3 text-left transition enabled:hover:border-accent-600 disabled:opacity-50">
                            <span class="text-sm font-semibold">{{ $v->label ?: 'Standar' }}</span>
                            <span class="text-base font-extrabold tabular-nums text-ink">{{ rp($vp) }}</span>
                            <span class="text-xs {{ $vout ? 'text-red-600' : 'text-ink-faint' }}">{{ $vout ? 'Habis' : 'Stok '.$v->stock }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ================= Bar draft (HP) ================= --}}
    <button type="button" @click="draft = true"
            class="fixed inset-x-3 bottom-[4.75rem] z-40 flex items-center justify-between rounded-2xl bg-accent-600 px-4 py-3 text-white shadow-pop md:hidden">
        <span class="text-sm font-bold tabular-nums">{{ $this->totals['count'] }} item · {{ rp($this->totals['total']) }}</span>
        <span class="flex items-center gap-1 text-sm font-bold">Catat <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 6l6 6-6 6"/></svg></span>
    </button>
</div>
