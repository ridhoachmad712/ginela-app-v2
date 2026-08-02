@php
    $st = \App\Models\StoreSetting::current();
    $methodLabel = ['CASH' => 'Tunai', 'QRIS' => 'QRIS', 'CARD' => 'Kartu'][$trx->method] ?? $trx->method;
    $after = $trx->subtotal - $trx->discount;
    $pointsEarned = $trx->member_id ? (int) floor($after * $st->point_per_rupiah) : 0;
@endphp
<div class="receipt-print mx-auto w-[280px] rounded-2xl border border-slate-200 bg-white p-4 text-left font-mono text-[12px] leading-tight text-black shadow-sm">
    <div class="text-center">
        @if ($st->logo_path)
            <img src="{{ asset('storage/' . $st->logo_path) }}" class="mx-auto mb-1 h-12 object-contain">
        @endif
        <div class="text-sm font-bold">{{ $st->name }}</div>
        @if ($st->address)<div>{{ $st->address }}</div>@endif
        @if ($st->phone)<div>{{ $st->phone }}</div>@endif
    </div>
    <div class="my-1.5 border-t border-dashed border-black"></div>
    <div class="flex justify-between"><span>{{ $trx->code }}</span><span>{{ $methodLabel }}{{ $trx->channel === 'ONLINE' ? ' · Online' : '' }}</span></div>
    <div class="flex justify-between"><span>{{ $trx->created_at->translatedFormat('d M Y, H.i') }}</span><span>Kasir: {{ $trx->cashier->name ?? '-' }}</span></div>
    @if ($trx->member)<div>Member: {{ $trx->member->name }}</div>@endif
    <div class="my-1.5 border-t border-dashed border-black"></div>
    @foreach ($trx->items as $it)
        <div class="mb-1"><div>{{ $it->name_snapshot }}</div>
            <div class="flex justify-between"><span>{{ $it->qty }} x {{ rp($it->unit_price) }}</span><span>{{ rp($it->line_total) }}</span></div>
        </div>
    @endforeach
    <div class="my-1.5 border-t border-dashed border-black"></div>
    <div class="flex justify-between"><span>Subtotal</span><span>{{ rp($trx->subtotal) }}</span></div>
    @if ($trx->discount > 0)<div class="flex justify-between"><span>Diskon</span><span>- {{ rp($trx->discount) }}</span></div>@endif
    @if ($trx->tax > 0)<div class="flex justify-between"><span>PPN</span><span>{{ rp($trx->tax) }}</span></div>@endif
    <div class="my-1 flex justify-between text-sm font-bold"><span>TOTAL</span><span>{{ rp($trx->total) }}</span></div>
    @if ($trx->method === 'CASH')
        <div class="flex justify-between"><span>Tunai</span><span>{{ rp($trx->paid) }}</span></div>
        <div class="flex justify-between"><span>Kembali</span><span>{{ rp($trx->change) }}</span></div>
    @endif
    @if ($pointsEarned > 0)
        <div class="my-1.5 border-t border-dashed border-black"></div>
        <div class="flex justify-between"><span>Poin didapat</span><span>+{{ $pointsEarned }}</span></div>
    @endif
    <div class="my-1.5 border-t border-dashed border-black"></div>
    <div class="text-center">Terima kasih telah berbelanja</div>
</div>
