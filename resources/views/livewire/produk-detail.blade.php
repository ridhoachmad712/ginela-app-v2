<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start gap-4">
            <a href="/produk" wire:navigate class="grid h-10 w-10 flex-none place-items-center rounded-xl border border-line text-ink-soft transition hover:bg-surface-3" aria-label="Kembali">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
            <span class="grid h-16 w-16 flex-none place-items-center overflow-hidden rounded-2xl bg-surface-3 text-3xl shadow-soft">
                @if ($product->image_path)<img src="{{ asset('storage/' . $product->image_path) }}" class="h-full w-full object-cover">@else{{ $product->emoji ?? '📦' }}@endif
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ $product->name }}</h1>
                    @if ($product->is_active)
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Aktif</span>
                    @else
                        <span class="rounded-full bg-surface-3 px-2 py-0.5 text-xs font-bold text-ink-soft">Arsip</span>
                    @endif
                </div>
                <p class="mt-0.5 text-sm text-ink-soft">{{ $product->category->name ?? 'Tanpa kategori' }} · per {{ $product->unit }} · {{ $product->attributes->count() ? $product->attributes->count().' atribut' : 'produk tunggal' }}</p>
            </div>
            @if ($isAdmin)
                <a href="/produk?edit={{ $product->id }}" wire:navigate class="flex items-center gap-1.5 rounded-xl bg-accent-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-accent-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                    Edit Produk
                </a>
            @endif
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @php
                $cards = [
                    ['Varian', number_format($summary['variants']), 'chip-accent'],
                    ['Total stok', number_format($summary['stock']).' '.$product->unit, 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400'],
                    ['Nilai inventaris', rp($summary['invValue']), 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
                    ['Perlu restock', number_format($summary['lowStock']).' varian', $summary['lowStock'] > 0 ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-surface-3 text-ink-faint'],
                ];
            @endphp
            @foreach ($cards as [$lab, $val, $chip])
                <div class="card p-4">
                    <div class="stat-ico {{ $chip }} text-sm font-bold">{{ mb_substr($lab, 0, 1) }}</div>
                    <div class="mt-3 text-xs font-semibold text-ink-soft">{{ $lab }}</div>
                    <div class="mt-0.5 text-xl font-extrabold tracking-tight tabular-nums text-ink">{{ $val }}</div>
                </div>
            @endforeach
        </div>

        {{-- Tabel varian: modal, harga, laba online (potong fee Shopee), margin, laba offline --}}
        <div class="overflow-x-auto card">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-3">
                <div>
                    <h2 class="text-sm font-bold">Rincian Varian</h2>
                    <p class="text-xs text-ink-faint">Laba online = harga online − (modal + fee Shopee)</p>
                </div>
                @if ($feeRate > 0)
                    <span class="rounded-lg bg-surface-3 px-2.5 py-1 text-xs font-semibold text-ink-soft">Fee Shopee {{ $product->category->name }}: {{ rtrim(rtrim(number_format($feeRate * 100, 2), '0'), '.') }}%</span>
                @endif
            </div>
            <table class="w-full min-w-[960px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-2 text-left text-xs uppercase tracking-wide text-ink-soft">
                        <th class="px-4 py-3 font-semibold">Varian</th>
                        <th class="px-4 py-3 font-semibold">SKU</th>
                        <th class="px-4 py-3 text-right font-semibold">Modal</th>
                        <th class="px-4 py-3 text-right font-semibold">Online</th>
                        <th class="px-4 py-3 text-right font-semibold">Laba online</th>
                        <th class="px-4 py-3 text-right font-semibold">Margin</th>
                        <th class="px-4 py-3 text-right font-semibold">Offline</th>
                        <th class="px-4 py-3 text-right font-semibold">Laba offline</th>
                        <th class="px-4 py-3 text-right font-semibold">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($variants as $v)
                        @php
                            $hppOn = $v->cost_price + round($v->online_price * $feeRate);
                            $labaOn = $v->online_price - $hppOn;
                            $marginOn = $hppOn > 0 ? round($labaOn / $hppOn * 100) : 0;
                            $labaOff = $v->offline_price - $v->cost_price;
                            $lowV = $v->stock <= $v->min_stock;
                        @endphp
                        <tr wire:key="v-{{ $v->id }}" class="border-b border-line-soft last:border-0">
                            <td class="px-4 py-3 font-semibold">{{ $v->label ?: 'Tunggal' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $v->sku ?: '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-soft">{{ $v->cost_price ? rp($v->cost_price) : '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ rp($v->online_price) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $labaOn >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">{{ rp($labaOn) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                @if ($v->cost_price > 0)
                                    <span class="font-semibold {{ $marginOn >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">{{ $marginOn }}%</span>
                                @else
                                    <span class="text-ink-faint">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ rp($v->offline_price) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $labaOff >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">{{ rp($labaOff) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $v->stock === 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : ($lowV ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-400') }}">
                                    {{ $v->stock }}{{ $lowV ? ' · min '.$v->min_stock : '' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-ink-faint">
            <b>Online</b>: laba = harga online − modal − fee Shopee ({{ rtrim(rtrim(number_format($feeRate * 100, 2), '0'), '.') }}%). ·
            <b>Offline</b>: laba = harga offline − modal (tanpa fee). ·
            Nilai inventaris = stok × modal. Ubah angka lewat <b>Edit Produk</b>.
        </p>
    </div>
</div>
