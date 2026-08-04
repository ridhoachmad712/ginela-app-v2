<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-2xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight">Pengaturan Toko</h1>
                <p class="text-sm text-ink-soft">Identitas toko &amp; tarif transaksi</p>
            </div>
        </div>

        @unless ($isAdmin)
            <div class="rounded-xl bg-amber-100 px-3 py-2.5 text-sm font-medium text-amber-700">Hanya admin yang dapat mengubah pengaturan. Kamu hanya bisa melihat.</div>
        @endunless

        {{-- Identitas --}}
        <section class="flex flex-col gap-4 card p-5">
            <h2 class="text-sm font-bold">Identitas Toko</h2>
            {{-- Logo --}}
            <div class="flex items-center gap-4 rounded-xl border border-line bg-surface-2 p-3">
                <div class="grid h-16 w-16 flex-none place-items-center overflow-hidden rounded-xl bg-surface">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="h-full w-full object-contain">
                    @elseif ($existingLogo)
                        <img src="{{ asset('storage/' . $existingLogo) }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-2xl font-extrabold text-accent-600">G</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-ink-soft">Logo toko (opsional, maks 1MB)</div>
                    @if ($isAdmin)
                        <input type="file" wire:model="logo" accept="image/*" class="mt-1 block w-full text-sm text-ink-soft file:mr-3 file:rounded-lg file:border-0 file:bg-accent-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <div wire:loading wire:target="logo" class="mt-1 text-xs text-accent-600">Mengunggah…</div>
                        @error('logo')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    @endif
                    <p class="mt-1 text-xs text-ink-faint">Muncul di struk &amp; halaman login.</p>
                </div>
            </div>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Nama toko</span>
                <input wire:model="name" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Alamat</span>
                <input wire:model="address" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Telepon</span>
                <input wire:model="phone" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
            <p class="text-xs text-ink-faint">Muncul di kepala struk.</p>
        </section>

        {{-- Tarif --}}
        <section class="flex flex-col gap-4 card p-5">
            <h2 class="text-sm font-bold">Tarif Transaksi</h2>
            <div class="grid grid-cols-2 gap-4">
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Pajak / PPN (%)</span>
                    <input wire:model="taxPct" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Diskon member (%)</span>
                    <input wire:model="discPct" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Poin per Rp1.000</span>
                    <input wire:model="pointPer1000" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Selisih harga offline (%)</span>
                    <input wire:model="offlineDiscPct" inputmode="decimal" @disabled(!$isAdmin) class="h-11 rounded-xl border border-line px-3 text-sm disabled:opacity-60"></label>
            </div>
            <p class="rounded-lg bg-surface-3 px-3 py-2 text-xs text-ink-soft">Toko non-PKP: setel <b>Pajak = 0</b>. <b>Selisih harga offline</b> = seberapa murah harga offline dari online (mis. 10% → offline = online − 10%). Perubahan langsung berlaku di perhitungan harga.</p>
        </section>

        {{-- Tampilan --}}
        @php $swatches = ['blue'=>'#2563eb','green'=>'#059669','violet'=>'#7c3aed','rose'=>'#e11d48','orange'=>'#ea580c','teal'=>'#0d9488']; @endphp
        <section class="flex flex-col gap-4 card p-5"
                 x-data="{ c: @entangle('themeColor'), dark: document.documentElement.classList.contains('dark') }">
            <h2 class="text-sm font-bold">Tampilan</h2>

            <div>
                <div class="mb-2 text-xs font-semibold text-ink-soft">Warna tema</div>
                <div class="flex flex-wrap gap-3">
                    @foreach ($swatches as $key => $hex)
                        <button type="button" @disabled(!$isAdmin)
                                @click="c='{{ $key }}'; document.documentElement.setAttribute('data-accent','{{ $key }}')"
                                class="h-10 w-10 rounded-full ring-2 ring-offset-2 ring-offset-surface transition disabled:opacity-50"
                                :class="c === '{{ $key }}' ? 'ring-ink scale-110' : 'ring-transparent'"
                                style="background: {{ $hex }}" aria-label="Warna {{ $key }}"></button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-ink-faint">Berlaku ke seluruh aplikasi &amp; halaman login setelah disimpan.</p>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-line bg-surface-2 px-4 py-3">
                <div>
                    <div class="text-sm font-semibold">Mode gelap</div>
                    <div class="text-xs text-ink-faint">Nyaman di mata, tersimpan di perangkat ini.</div>
                </div>
                <button type="button" @click="ginelaToggleDark(); dark=document.documentElement.classList.contains('dark')"
                        class="relative h-7 w-12 flex-none rounded-full transition" :class="dark ? 'bg-accent-600' : 'bg-surface-3'">
                    <span class="absolute top-0.5 h-6 w-6 rounded-full bg-white shadow transition-all" :class="dark ? 'left-[22px]' : 'left-0.5'"></span>
                </button>
            </div>
        </section>

        @if ($error)<div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700">{{ $error }}</div>@endif

        @if ($isAdmin)
            <div class="flex items-center justify-end gap-3">
                @if ($saved)<span class="text-sm font-semibold text-green-600">Tersimpan ✓</span>@endif
                <button wire:click="save" class="rounded-xl bg-accent-600 px-6 py-3 text-sm font-bold text-white hover:bg-accent-700">Simpan Pengaturan</button>
            </div>
        @endif
    </div>
</div>
