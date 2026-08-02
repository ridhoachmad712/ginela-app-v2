@php $t = $this->totals; @endphp

{{-- ============ Layar sukses + struk ============ --}}
@if ($done)
    @php $st = \App\Models\StoreSetting::current(); $d = $done; @endphp
    <div class="fixed inset-0 z-[55] flex flex-col overflow-y-auto bg-surface-3 print:bg-surface">
        <div class="mx-auto flex w-full max-w-md flex-1 flex-col items-center justify-center gap-4 p-6 text-center">
            <div class="grid h-20 w-20 animate-check-pop place-items-center rounded-full bg-green-100 text-green-600 print:hidden">
                <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="animate-slide-up print:hidden">
                <h2 class="text-2xl font-extrabold tracking-tight">Pembayaran Berhasil</h2>
                <p class="text-sm text-ink-soft">{{ $d['code'] }} · Total {{ rp($d['total']) }}</p>
            </div>
            @if ($d['method'] === 'CASH')
                <p class="text-base font-bold tabular-nums print:hidden">Kembalian: {{ rp($d['change']) }}</p>
            @endif
            @if ($d['pointsEarned'] > 0 && $d['member'])
                <span class="rounded-full bg-accent-100 px-3 py-1 text-sm font-semibold text-accent-700 print:hidden">+{{ $d['pointsEarned'] }} poin untuk {{ $d['member']['name'] }}</span>
            @endif

            {{-- Struk thermal --}}
            <div class="receipt-print mx-auto w-[280px] rounded-2xl border border-slate-200 bg-white p-4 text-left font-mono text-[12px] leading-tight text-black shadow-sm">
                <div class="text-center">
                    <div class="text-sm font-bold">{{ $st->name }}</div>
                    @if ($st->address)<div>{{ $st->address }}</div>@endif
                    @if ($st->phone)<div>{{ $st->phone }}</div>@endif
                </div>
                <div class="my-1.5 border-t border-dashed border-black"></div>
                <div class="flex justify-between"><span>{{ $d['code'] }}</span><span>{{ ['CASH'=>'Tunai','QRIS'=>'QRIS','CARD'=>'Kartu'][$d['method']] }}</span></div>
                <div class="flex justify-between"><span>{{ \Carbon\Carbon::createFromTimestamp($d['at'])->translatedFormat('d M Y, H.i') }}</span><span>Kasir: {{ $d['cashier'] }}</span></div>
                @if ($d['member'])<div>Member: {{ $d['member']['name'] }}</div>@endif
                <div class="my-1.5 border-t border-dashed border-black"></div>
                @foreach ($d['lines'] as $l)
                    <div class="mb-1"><div>{{ $l['name'] }}</div>
                        <div class="flex justify-between"><span>{{ $l['qty'] }} x {{ rp($l['price']) }}</span><span>{{ rp($l['price'] * $l['qty']) }}</span></div>
                    </div>
                @endforeach
                <div class="my-1.5 border-t border-dashed border-black"></div>
                <div class="flex justify-between"><span>Subtotal</span><span>{{ rp($d['subtotal']) }}</span></div>
                @if ($d['discount'] > 0)<div class="flex justify-between"><span>Diskon</span><span>- {{ rp($d['discount']) }}</span></div>@endif
                @if ($d['tax'] > 0)<div class="flex justify-between"><span>PPN</span><span>{{ rp($d['tax']) }}</span></div>@endif
                <div class="my-1 flex justify-between text-sm font-bold"><span>TOTAL</span><span>{{ rp($d['total']) }}</span></div>
                @if ($d['method'] === 'CASH')
                    <div class="flex justify-between"><span>Tunai</span><span>{{ rp($d['paid']) }}</span></div>
                    <div class="flex justify-between"><span>Kembali</span><span>{{ rp($d['change']) }}</span></div>
                @endif
                @if ($d['pointsEarned'] > 0 && $d['member'])
                    <div class="my-1.5 border-t border-dashed border-black"></div>
                    <div class="flex justify-between"><span>Poin didapat</span><span>+{{ $d['pointsEarned'] }}</span></div>
                    <div class="flex justify-between"><span>Total poin</span><span>{{ $d['member']['points'] + $d['pointsEarned'] }}</span></div>
                @endif
                <div class="my-1.5 border-t border-dashed border-black"></div>
                <div class="text-center">Terima kasih telah berbelanja</div>
            </div>

            <div class="flex w-full gap-3 print:hidden">
                <button onclick="window.print()" class="h-12 flex-1 rounded-xl border border-line font-semibold">Cetak Struk</button>
                <button wire:click="newTransaction" class="h-12 flex-1 rounded-xl bg-accent-600 font-semibold text-white">Transaksi Baru</button>
            </div>
        </div>
    </div>

{{-- ============ Layar pembayaran ============ --}}
@elseif ($paying)
    @php $change = $received - $t['total']; $kurang = $change < 0; @endphp
    <div class="fixed inset-0 z-[55] flex flex-col bg-surface-3">
        <header class="flex items-center gap-3 border-b border-line bg-surface px-4 py-3">
            <button wire:click="closePayment" class="grid h-9 w-9 place-items-center rounded-lg border border-line text-ink-soft">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <h1 class="text-base font-bold tracking-tight">Pembayaran</h1>
            <span class="ml-auto text-xs tabular-nums text-ink-faint">{{ $t['count'] }} item</span>
        </header>

        <div class="flex min-h-0 flex-1 flex-col md:flex-row">
            {{-- Recap --}}
            <aside class="flex flex-col border-b border-line bg-surface md:w-[340px] md:flex-none md:border-b-0 md:border-r">
                <div class="border-b border-line px-5 py-3">
                    <div class="mb-2 text-xs font-semibold text-ink-soft">Jenis Harga</div>
                    <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                        <button wire:click="$set('channel','OFFLINE')" class="rounded-xl border py-2.5 transition {{ $channel === 'OFFLINE' ? 'border-accent-600 bg-accent-50 text-accent-600' : 'border-line text-ink-soft' }}">Offline (toko)</button>
                        <button wire:click="$set('channel','ONLINE')" class="rounded-xl border py-2.5 transition {{ $channel === 'ONLINE' ? 'border-accent-600 bg-accent-50 text-accent-600' : 'border-line text-ink-soft' }}">Online</button>
                    </div>
                </div>
                <div class="border-b border-line px-5 py-3">
                    @if ($this->member)
                        <div class="flex items-center gap-2 rounded-xl bg-accent-50 px-3 py-2.5 text-sm">
                            <span class="min-w-0 flex-1"><span class="block truncate font-semibold text-accent-700">{{ $this->member->name }}</span><span class="text-xs text-ink-faint">{{ $this->member->points }} poin</span></span>
                            <button wire:click="clearMember" class="text-ink-faint hover:text-red-600">✕</button>
                        </div>
                    @else
                        <button wire:click="$set('pickMember', true)" class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-line px-3 py-2.5 text-sm font-semibold text-ink-soft hover:border-accent-500 hover:text-accent-600">
                            + Tambah member (opsional)
                        </button>
                    @endif
                </div>
                <div class="mt-auto space-y-1.5 border-t border-line px-5 py-4 text-sm">
                    <div class="flex justify-between text-ink-soft"><span>Subtotal</span><span class="font-semibold tabular-nums text-ink">{{ rp($t['subtotal']) }}</span></div>
                    @if ($t['discount'] > 0)<div class="flex justify-between text-ink-soft"><span>Diskon member</span><span class="font-semibold tabular-nums text-green-600">− {{ rp($t['discount']) }}</span></div>@endif
                    @if ($t['taxRate'] > 0)<div class="flex justify-between text-ink-soft"><span>PPN {{ round($t['taxRate']*100) }}%</span><span class="font-semibold tabular-nums text-ink">{{ rp($t['tax']) }}</span></div>@endif
                    <div class="flex items-baseline justify-between border-t border-dashed border-line pt-2"><span class="font-semibold text-ink-soft">Total</span><span class="text-2xl font-extrabold tracking-tight tabular-nums">{{ rp($t['total']) }}</span></div>
                </div>
            </aside>

            {{-- Panel metode --}}
            <section class="flex min-h-0 flex-1 flex-col overflow-y-auto p-5">
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['CASH'=>'Tunai','QRIS'=>'QRIS','CARD'=>'Kartu'] as $mk => $ml)
                        <button wire:click="$set('method','{{ $mk }}')"
                                class="rounded-2xl border p-3 text-sm font-semibold transition {{ $method === $mk ? 'border-accent-600 bg-accent-50 text-accent-600' : 'border-line bg-surface text-ink-soft' }}">
                            {{ $ml }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-4 flex-1">
                    @if ($method === 'CASH')
                        <div class="rounded-2xl border border-line bg-surface p-4">
                            <div class="text-xs font-semibold text-ink-soft">Uang diterima</div>
                            <div class="text-3xl font-extrabold tabular-nums"><span class="mr-1 text-lg text-ink-faint">Rp</span>{{ number_format($received, 0, ',', '.') }}</div>
                            <div class="mt-2 flex justify-between border-t border-dashed border-line pt-2 text-sm">
                                <span class="font-semibold text-ink-soft">{{ $kurang ? 'Kurang' : 'Kembalian' }}</span>
                                <span class="text-lg font-extrabold tabular-nums {{ $kurang ? 'text-red-600' : 'text-green-600' }}">{{ rp(abs($change)) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-4 gap-2">
                            <button wire:click="exactCash" class="rounded-xl border border-accent-600 bg-accent-50 px-2 py-2.5 text-sm font-bold text-accent-600">Uang Pas</button>
                            @foreach (\App\Livewire\Kasir::QUICK_CASH as $qc)
                                <button wire:click="setCash({{ $qc }})" class="rounded-xl border border-line bg-surface px-2 py-2.5 text-sm font-bold tabular-nums hover:border-accent-500">{{ number_format($qc, 0, ',', '.') }}</button>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach (['1','2','3','4','5','6','7','8','9','000','0'] as $k)
                                <button wire:click="tapDigit('{{ $k }}')" class="rounded-xl border border-line bg-surface py-3.5 text-xl font-bold tabular-nums active:scale-95">{{ $k }}</button>
                            @endforeach
                            <button wire:click="delDigit" class="grid place-items-center rounded-xl border border-line bg-surface text-red-600 active:scale-95">⌫</button>
                        </div>
                    @elseif ($method === 'QRIS')
                        <div class="flex flex-col items-center gap-3 py-6 text-center">
                            <div class="grid h-48 w-48 place-items-center rounded-2xl border border-line bg-surface text-6xl">🔲</div>
                            <div class="text-lg font-bold tabular-nums">{{ rp($t['total']) }}</div>
                            <p class="text-sm text-ink-soft">Tunjukkan QR ke pelanggan, lalu tekan Selesaikan.</p>
                            <p class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-700">Catatan: QRIS asli butuh integrasi payment gateway.</p>
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-4 py-8 text-center">
                            <div class="flex h-40 w-64 flex-col justify-between rounded-2xl bg-gradient-to-br from-accent-600 to-sky-400 p-4 text-white">
                                <div class="h-7 w-9 rounded bg-white/60"></div>
                                <div class="tracking-widest tabular-nums">•••• •••• •••• 6411</div>
                                <div class="flex justify-between text-[10px] opacity-90"><span>GINELA STORE</span><span>DEBIT</span></div>
                            </div>
                            <p class="text-sm text-ink-soft">Proses di mesin EDC, lalu tekan Selesaikan setelah disetujui.</p>
                        </div>
                    @endif
                </div>

                @if ($payError)
                    <div class="mt-3 rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700">{{ $payError }}</div>
                @endif

                <button wire:click="confirm" wire:loading.attr="disabled"
                        @disabled($method === 'CASH' && $received < $t['total'])
                        class="mt-4 h-14 w-full rounded-2xl bg-accent-600 text-base font-bold text-white transition hover:bg-accent-700 disabled:bg-surface-3 disabled:text-ink-faint">
                    <span wire:loading.remove wire:target="confirm">{{ $method === 'CASH' && $received < $t['total'] ? 'Nominal kurang' : 'Selesaikan Pembayaran' }}</span>
                    <span wire:loading wire:target="confirm">Memproses…</span>
                </button>
            </section>
        </div>

        {{-- Pemilih member --}}
        @if ($pickMember)
            <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
                <div class="absolute inset-0 bg-black/50" wire:click="$set('pickMember', false)"></div>
                <div class="relative flex max-h-[80vh] w-full flex-col rounded-t-3xl bg-surface p-4 sm:max-w-md sm:rounded-3xl">
                    <div class="mb-3 flex items-center gap-2"><h3 class="text-base font-bold">Pilih Member</h3><button wire:click="$set('pickMember', false)" class="ml-auto text-ink-faint">✕</button></div>
                    <input wire:model.live.debounce.300ms="memberQuery" placeholder="Cari nama / nomor HP…" class="mb-3 h-11 rounded-xl border border-line px-3 text-sm">
                    <div class="min-h-0 flex-1 space-y-1.5 overflow-y-auto">
                        @foreach ($this->memberList as $m)
                            <button wire:key="m-{{ $m->id }}" wire:click="setMember({{ $m->id }})" class="flex w-full items-center gap-3 rounded-xl border border-line p-3 text-left hover:border-accent-500">
                                <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold">{{ $m->name }}</span><span class="block text-xs text-ink-faint tabular-nums">{{ $m->phone }}</span></span>
                                <span class="text-xs font-semibold tabular-nums text-ink-faint">{{ $m->points }} poin</span>
                            </button>
                        @endforeach
                        @if ($this->memberList->isEmpty())<p class="py-6 text-center text-sm text-ink-faint">Member tidak ditemukan.</p>@endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
