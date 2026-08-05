@php $t = $this->totals; @endphp
<div class="flex h-full min-h-0 flex-col">
    <div class="flex items-center gap-2 border-b border-line px-5 py-4">
        <h2 class="text-lg font-bold tracking-tight">Catat Penjualan</h2>
        <span class="grid h-6 min-w-6 place-items-center rounded-full bg-accent-600 px-1.5 text-xs font-bold text-white">{{ $t['count'] }}</span>
        @if (! empty($cart))
            <button wire:click="clearCart" class="ml-auto rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/15">Kosongkan</button>
        @endif
    </div>

    {{-- Channel + tanggal + metode --}}
    <div class="flex flex-col gap-3 border-b border-line px-5 py-3">
        <div>
            <div class="mb-1.5 text-xs font-semibold text-ink-soft">Channel penjualan</div>
            <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                <button wire:click="$set('channel','ONLINE')" class="rounded-xl border-2 py-2.5 transition {{ $channel === 'ONLINE' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft hover:bg-surface-3' }}">🛒 Shopee</button>
                <button wire:click="$set('channel','OFFLINE')" class="rounded-xl border-2 py-2.5 transition {{ $channel === 'OFFLINE' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft hover:bg-surface-3' }}">🏪 Offline</button>
            </div>
        </div>
        <div class="flex items-start gap-3">
            <label class="flex flex-1 flex-col gap-1">
                <span class="text-xs font-semibold text-ink-soft">Tanggal</span>
                <input type="date" wire:model="saleDate" max="{{ now()->format('Y-m-d') }}" class="h-10 rounded-lg border border-line bg-surface px-2 text-sm">
            </label>
            @if ($channel === 'OFFLINE')
                <div class="flex flex-1 flex-col gap-1">
                    <span class="text-xs font-semibold text-ink-soft">Metode</span>
                    <div class="grid grid-cols-3 gap-1 text-[11px] font-semibold">
                        @foreach (['TUNAI' => 'Tunai', 'TRANSFER' => 'Transfer', 'QRIS' => 'QRIS'] as $k => $lbl)
                            <button wire:click="$set('method','{{ $k }}')" class="rounded-lg border py-2 transition {{ $method === $k ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft' }}">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Item --}}
    @if (empty($cart))
        <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center text-ink-faint">
            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="7" width="16" height="14" rx="2"/><path d="M8 7V5a4 4 0 018 0v2"/></svg>
            <p class="text-sm">Belum ada item.<br>Tap produk untuk menambah ke catatan.</p>
        </div>
    @else
        <div class="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 py-3">
            @foreach ($cart as $vid => $line)
                @php $price = $this->priceOf($line); @endphp
                <div wire:key="c-{{ $vid }}" class="flex items-center gap-3 rounded-xl border border-line bg-surface-2 p-2.5">
                    <span class="grid h-10 w-10 flex-none place-items-center rounded-lg bg-surface text-lg">{{ $line['emoji'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $line['product'] }}@if ($line['label'])<span class="text-ink-faint"> · {{ $line['label'] }}</span>@endif</p>
                        <p class="text-xs tabular-nums text-ink-faint">{{ rp($price) }} × {{ $line['qty'] }} = <b class="text-ink">{{ rp($price * $line['qty']) }}</b></p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="dec({{ $vid }})" class="grid h-8 w-8 place-items-center rounded-lg border border-line text-lg font-bold leading-none text-accent-600 hover:bg-accent-50 dark:hover:bg-accent-500/15">−</button>
                        <span class="w-6 text-center text-sm font-bold tabular-nums">{{ $line['qty'] }}</span>
                        <button wire:click="inc({{ $vid }})" class="grid h-8 w-8 place-items-center rounded-lg border border-line text-lg font-bold leading-none text-accent-600 hover:bg-accent-50 dark:hover:bg-accent-500/15">+</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Total + simpan --}}
    <div class="border-t border-line px-5 py-4">
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between text-ink-soft"><dt>Subtotal</dt><dd class="font-semibold tabular-nums text-ink">{{ rp($t['subtotal']) }}</dd></div>
            @if ($channel === 'ONLINE' && $t['shopeeFee'] > 0)
                <div class="flex justify-between text-ink-soft"><dt>Est. fee Shopee</dt><dd class="tabular-nums">− {{ rp($t['shopeeFee']) }}</dd></div>
            @endif
            @if ($t['taxRate'] > 0)
                <div class="flex justify-between text-ink-soft"><dt>PPN {{ round($t['taxRate'] * 100) }}%</dt><dd class="tabular-nums">{{ rp($t['tax']) }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-ink-soft">Est. laba</dt><dd class="font-semibold tabular-nums {{ $t['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">{{ rp($t['profit']) }}</dd></div>
        </dl>
        <div class="mt-3 flex items-baseline justify-between border-t border-dashed border-line pt-3">
            <span class="text-sm font-semibold text-ink-soft">Total</span>
            <span class="text-2xl font-extrabold tracking-tight tabular-nums">{{ rp($t['total']) }}</span>
        </div>
        @if ($error)<div class="mt-2 rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-500/15 dark:text-red-400">{{ $error }}</div>@endif
        <button wire:click="save" @disabled(empty($cart))
                class="mt-3 flex h-13 w-full items-center justify-center gap-2 rounded-2xl bg-accent-600 py-3.5 text-base font-bold text-white transition hover:bg-accent-700 disabled:bg-surface-3 disabled:text-ink-faint">
            <span wire:loading.remove wire:target="save">{{ empty($cart) ? 'Tambahkan item dulu' : 'Simpan Penjualan' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
    </div>
</div>
