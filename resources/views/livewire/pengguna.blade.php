<div class="min-h-0 flex-1 overflow-y-auto">
    <div class="mx-auto flex max-w-3xl flex-col gap-5 px-5 py-6 pb-24 md:pb-6">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-accent-50 text-accent-600 dark:bg-accent-500/15 dark:text-accent-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13A4 4 0 0116 11"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold tracking-tight">Kelola Pengguna</h1>
                <p class="text-sm text-ink-soft">Akun Admin &amp; Kasir · ganti kata sandi</p>
            </div>
            <button wire:click="openNew" class="rounded-xl bg-accent-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-accent-700">+ Tambah</button>
        </div>

        @if ($error && ! $mode)
            <div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700 dark:bg-red-500/15 dark:text-red-400">{{ $error }}</div>
        @endif

        <div class="flex flex-col gap-2">
            @foreach ($this->users as $u)
                <div wire:key="u-{{ $u->id }}" class="flex items-center gap-3 card p-4">
                    <div class="grid h-11 w-11 flex-none place-items-center rounded-full text-sm font-bold {{ $u->role === 'ADMIN' ? 'bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'bg-surface-3 text-ink-soft' }}">
                        {{ strtoupper(mb_substr($u->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-semibold">{{ $u->name }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $u->role === 'ADMIN' ? 'bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'bg-surface-3 text-ink-soft' }}">{{ $u->role === 'ADMIN' ? 'Admin' : 'Kasir' }}</span>
                            @if ($u->id === auth()->id())<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Anda</span>@endif
                            @unless ($u->is_active)<span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-400">Nonaktif</span>@endunless
                        </div>
                        <div class="text-xs text-ink-faint">Login: pilih peran <b>{{ $u->role === 'ADMIN' ? 'Admin' : 'Kasir' }}</b> + kata sandi</div>
                    </div>
                    <button wire:click="openEdit({{ $u->id }})" class="rounded-lg border border-line px-3 py-1.5 text-xs font-semibold transition hover:bg-surface-3">Edit</button>
                    @if ($u->id !== auth()->id())
                        <button wire:click="$set('deletingId', {{ $u->id }})" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:hover:bg-red-500/15">Hapus</button>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-xs text-ink-faint">Aplikasi ini login per <b>peran</b> (satu pengguna aktif per peran). Untuk mengganti kata sandi, tekan <b>Edit</b> lalu isi kata sandi baru.</p>
    </div>

    {{-- Modal tambah / edit --}}
    @if ($mode)
        <div class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>
            <div class="relative w-full rounded-t-3xl bg-surface shadow-pop sm:max-w-md sm:rounded-3xl">
                <div class="flex items-center gap-2 border-b border-line px-5 py-4">
                    <h3 class="text-base font-bold">{{ $mode === 'new' ? 'Tambah Pengguna' : 'Edit Pengguna' }}</h3>
                    <button wire:click="close" class="ml-auto text-ink-faint hover:text-ink">✕</button>
                </div>
                <div class="flex flex-col gap-4 px-5 py-4">
                    <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">Nama</span>
                        <input wire:model="fName" class="h-11 rounded-xl border border-line bg-surface px-3 text-sm" placeholder="mis. Ridho"></label>

                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold text-ink-soft">Peran</span>
                        <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                            <button type="button" wire:click="$set('fRole','ADMIN')" class="rounded-xl border-2 py-2.5 transition {{ $fRole === 'ADMIN' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft' }}">Admin</button>
                            <button type="button" wire:click="$set('fRole','KASIR')" class="rounded-xl border-2 py-2.5 transition {{ $fRole === 'KASIR' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft' }}">Kasir</button>
                        </div>
                        <p class="text-xs text-ink-faint">Admin bisa mengatur semua. Kasir hanya transaksi & lihat.</p>
                    </div>

                    <label class="flex flex-col gap-1.5"><span class="text-xs font-semibold text-ink-soft">{{ $mode === 'new' ? 'Kata sandi' : 'Kata sandi baru' }}</span>
                        <input wire:model="fPassword" type="password" autocomplete="new-password" class="h-11 rounded-xl border border-line bg-surface px-3 text-sm" placeholder="{{ $mode === 'new' ? 'Minimal 6 karakter' : 'Kosongkan bila tak diganti' }}">
                        @if ($mode === 'edit')<span class="text-xs text-ink-faint">Biarkan kosong untuk mempertahankan kata sandi lama.</span>@endif
                    </label>

                    <label class="flex items-center justify-between rounded-xl border border-line bg-surface-2 px-4 py-3">
                        <span class="text-sm font-semibold">Akun aktif</span>
                        <input type="checkbox" wire:model="fActive" class="h-5 w-5 rounded border-line text-accent-600 focus:ring-accent-500">
                    </label>

                    @if ($error)<div class="rounded-xl bg-red-100 px-3 py-2.5 text-sm font-medium text-red-700 dark:bg-red-500/15 dark:text-red-400">{{ $error }}</div>@endif
                </div>
                <div class="flex gap-3 border-t border-line px-5 py-4">
                    <button wire:click="close" class="h-12 flex-1 rounded-xl border border-line font-semibold">Batal</button>
                    <button wire:click="save" class="h-12 flex-1 rounded-xl bg-accent-600 font-semibold text-white hover:bg-accent-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Konfirmasi hapus --}}
    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('deletingId', null)"></div>
            <div class="relative w-full max-w-sm rounded-3xl bg-surface p-6 text-center shadow-pop">
                <h3 class="text-lg font-bold">Hapus pengguna?</h3>
                <p class="mt-1 text-sm text-ink-soft">Akun akan dihapus permanen. Bila punya riwayat transaksi, nonaktifkan saja.</p>
                <div class="mt-5 flex gap-3">
                    <button wire:click="$set('deletingId', null)" class="h-12 flex-1 rounded-xl border border-line font-semibold">Batal</button>
                    <button wire:click="delete" class="h-12 flex-1 rounded-xl bg-red-600 font-semibold text-white">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
