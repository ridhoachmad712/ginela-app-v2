<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-2xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight">Pengaturan Toko</h1>
                <p class="text-sm text-slate-500">Identitas toko &amp; tarif transaksi</p>
            </div>
        </div>

        @unless ($isAdmin)
            <div class="rounded-xl bg-amber-100 px-3 py-2.5 text-sm font-medium text-amber-700">Hanya admin yang dapat mengubah pengaturan. Kamu hanya bisa melihat.</div>
        @endunless

        {{-- Identitas --}}
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-bold">Identitas Toko</h2>
            {{-- Logo --}}
            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="grid h-16 w-16 flex-none place-items-center overflow-hidden rounded-xl bg-white">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="h-full w-full object-contain">
                    @elseif ($existingLogo)
                        <img src="{{ asset('storage/' . $existingLogo) }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-2xl font-extrabold text-blue-600">G</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-slate-500">Logo toko (opsional, maks 1MB)</div>
                    @if ($isAdmin)
                        <input type="file" wire:model="logo" accept="image/*" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                        <div wire:loading wire:target="logo" class="mt-1 text-xs text-blue-600">Mengunggah…</div>
                        @error('logo')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                    @endif
                    <p class="mt-1 text-xs text-slate-400">Muncul di struk &amp; halaman login.</p>
                </div>
            </div>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Nama toko</span>
                <input wire:model="name" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Alamat</span>
                <input wire:model="address" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
            <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Telepon</span>
                <input wire:model="phone" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
            <p class="text-xs text-slate-400">Muncul di kepala struk.</p>
        </section>

        {{-- Tarif --}}
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-bold">Tarif Transaksi</h2>
            <div class="grid grid-cols-2 gap-4">
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Pajak / PPN (%)</span>
                    <input wire:model="taxPct" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Diskon member (%)</span>
                    <input wire:model="discPct" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
                <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-slate-500">Poin per Rp1.000</span>
                    <input wire:model="pointPer1000" inputmode="numeric" @disabled(!$isAdmin) class="h-11 rounded-xl border border-slate-200 px-3 text-sm disabled:opacity-60"></label>
            </div>
            <p class="rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-500">Toko non-PKP: setel <b>Pajak = 0</b>. Perubahan langsung berlaku di kasir, pembayaran &amp; struk.</p>
        </section>

        @if ($error)<div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700">{{ $error }}</div>@endif

        @if ($isAdmin)
            <div class="flex items-center justify-end gap-3">
                @if ($saved)<span class="text-sm font-semibold text-green-600">Tersimpan ✓</span>@endif
                <button wire:click="save" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white hover:bg-blue-700">Simpan Pengaturan</button>
            </div>
        @endif
    </div>
</div>
