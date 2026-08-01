<div class="flex h-full min-h-0 flex-col">
    <div class="flex items-center gap-2 border-b border-slate-200 px-5 py-4">
        <h2 class="text-lg font-bold tracking-tight">Keranjang</h2>
        <span class="grid h-6 min-w-6 place-items-center rounded-full bg-blue-600 px-1.5 text-xs font-bold text-white">{{ $this->totals['count'] }}</span>
        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">{{ $channel === 'OFFLINE' ? 'Offline' : 'Online' }}</span>
        @if (! empty($cart))
            <button wire:click="clearCart" class="ml-auto rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Kosongkan</button>
        @endif
    </div>

    @if ($this->member)
        <div class="flex items-center gap-2 border-b border-slate-200 bg-blue-50/60 px-5 py-2 text-sm">
            <span class="font-semibold text-blue-600">{{ $this->member->name }}</span>
            <span class="ml-auto text-xs tabular-nums text-slate-400">{{ $this->member->points }} poin</span>
        </div>
    @endif

    @if (empty($cart))
        <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center text-slate-400">
            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/><path d="M2 3h3l2.4 12.4a2 2 0 002 1.6h8.5a2 2 0 002-1.6L23 7H6"/></svg>
            <p class="text-sm">Keranjang masih kosong.<br>Tap produk untuk menambahkan.</p>
        </div>
    @else
        <div class="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 py-3">
            @foreach ($cart as $vid => $line)
                @php $price = $channel === 'ONLINE' ? $line['online'] : $line['offline']; @endphp
                <div wire:key="c-{{ $vid }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                    <span class="grid h-10 w-10 flex-none place-items-center rounded-lg bg-white text-lg">{{ $line['emoji'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $line['product'] }}@if ($line['label'])<span class="text-slate-400"> · {{ $line['label'] }}</span>@endif</p>
                        <p class="text-xs tabular-nums text-slate-400">{{ rp($price) }} × {{ $line['qty'] }}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="dec({{ $vid }})" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-lg font-bold leading-none text-blue-600 hover:bg-blue-50">−</button>
                        <span class="w-6 text-center text-sm font-bold tabular-nums">{{ $line['qty'] }}</span>
                        <button wire:click="add({{ $vid }})" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-lg font-bold leading-none text-blue-600 hover:bg-blue-50">+</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="border-t border-slate-200 px-5 py-4">
        @php $t = $this->totals; @endphp
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between text-slate-500"><dt>Subtotal</dt><dd class="font-semibold tabular-nums text-slate-800">{{ rp($t['subtotal']) }}</dd></div>
            @if ($t['discount'] > 0)
                <div class="flex justify-between text-slate-500"><dt>Diskon member {{ round($t['discRate'] * 100) }}%</dt><dd class="font-semibold tabular-nums text-green-600">− {{ rp($t['discount']) }}</dd></div>
            @endif
            @if ($t['taxRate'] > 0)
                <div class="flex justify-between text-slate-500"><dt>PPN {{ round($t['taxRate'] * 100) }}%</dt><dd class="font-semibold tabular-nums text-slate-800">{{ rp($t['tax']) }}</dd></div>
            @endif
        </dl>
        <div class="mt-3 flex items-baseline justify-between border-t border-dashed border-slate-200 pt-3">
            <span class="text-sm font-semibold text-slate-500">Total</span>
            <span class="text-2xl font-extrabold tracking-tight tabular-nums">{{ rp($t['total']) }}</span>
        </div>
        <button wire:click="openPayment" @disabled(empty($cart))
                class="mt-4 h-14 w-full rounded-2xl bg-blue-600 text-base font-bold text-white transition hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400">
            {{ empty($cart) ? 'Pilih produk dulu' : 'Bayar · ' . rp($t['total']) }}
        </button>
    </div>
</div>
