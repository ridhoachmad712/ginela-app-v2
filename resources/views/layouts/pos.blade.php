<!DOCTYPE html>
@php $store = \App\Models\StoreSetting::current(); @endphp
<html lang="id" class="h-full" data-accent="{{ $store->theme_color ?? 'blue' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Ginela ERP' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function () {
            try {
                var d = localStorage.getItem('ginela-dark');
                if (d === '1' || (d === null && window.matchMedia('(prefers-color-scheme:dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
            window.ginelaToggleDark = function () {
                var on = document.documentElement.classList.toggle('dark');
                try { localStorage.setItem('ginela-dark', on ? '1' : '0'); } catch (e) {}
            };
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none!important}
        @media print {
            body * { visibility: hidden !important; }
            .receipt-print, .receipt-print * { visibility: visible !important; color:#000 !important; background:#fff !important; }
            .receipt-print { position: absolute; left: 0; top: 0; border: 0 !important; }
        }
    </style>
</head>
<body class="h-full bg-page text-ink antialiased">
@php
    $isAdmin = (bool) auth()->user()?->isAdmin();
    $groups = [
        [null, [
            ['/dashboard', 'Beranda', 'M3 12l9-9 9 9M5 10v10h14V10'],
        ]],
        ['Katalog', [
            ['/produk', 'Produk', 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7'],
        ]],
        ['Inventori', [
            ['/stok', 'Stok', 'M4 7h16M4 12h16M4 17h16'],
        ]],
        ['Penjualan', [
            ['/kasir', 'Penjualan', 'M3 3h2l2.4 12.4a2 2 0 002 1.6h8.5a2 2 0 002-1.6L23 6H6M9 20a1 1 0 100 2 1 1 0 000-2zM18 20a1 1 0 100 2 1 1 0 000-2z'],
            ['/transaksi', 'Riwayat', 'M9 2v4M15 2v4M4 5h16v17H4zM8 11h8M8 15h5'],
        ]],
        ['Analisis', [
            ['/laporan', 'Laporan', 'M3 3v18h18M7 14l3-3 3 2 4-5'],
        ]],
        ['Pengaturan', array_values(array_filter([
            ['/pengaturan', 'Toko', 'M3 9l1.5-5h15L21 9M4 9v11h16V9M4 9h16M9 20v-6h6v6'],
            $isAdmin ? ['/pengguna', 'Pengguna', 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13A4 4 0 0116 11'] : null,
        ]))],
    ];
    $path = '/'.request()->path();
    $isActive = fn ($url) => str_starts_with($path, $url) || ($url === '/dashboard' && $path === '/');

    $mobile = [
        ['/dashboard', 'Beranda', 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['/produk', 'Produk', 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7'],
        ['/stok', 'Stok', 'M4 7h16M4 12h16M4 17h16'],
        ['/kasir', 'Jual', 'M3 3h2l2.4 12.4a2 2 0 002 1.6h8.5a2 2 0 002-1.6L23 6H6M9 20a1 1 0 100 2 1 1 0 000-2zM18 20a1 1 0 100 2 1 1 0 000-2z'],
    ];
@endphp

<div class="flex h-[100dvh] overflow-hidden">
    {{-- Sidebar bermodul — tablet & desktop --}}
    <aside class="hidden w-60 flex-none flex-col border-r border-line bg-surface md:flex">
        {{-- Brand --}}
        <div class="flex items-center gap-2.5 border-b border-line px-4 py-4">
            <div class="grid h-10 w-10 flex-none place-items-center overflow-hidden rounded-xl bg-accent-600 text-lg font-extrabold text-white">
                @if ($store->logo_path)<img src="{{ asset('storage/' . $store->logo_path) }}" class="h-full w-full bg-white object-contain">@else G @endif
            </div>
            <div class="min-w-0">
                <div class="truncate font-bold leading-tight">{{ $store->name }}</div>
                <div class="text-[11px] font-medium text-ink-faint">ERP Produk &amp; Stok</div>
            </div>
        </div>

        {{-- Menu --}}
        <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-3">
            @foreach ($groups as [$title, $items])
                @if ($title)
                    <div class="px-2 pb-1 pt-3 text-[10px] font-bold uppercase tracking-wider text-ink-faint">{{ $title }}</div>
                @endif
                @foreach ($items as [$url, $label, $icon])
                    @php $active = $isActive($url); @endphp
                    <a href="{{ $url }}" wire:navigate
                       class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition {{ $active ? 'chip-accent' : 'text-ink-soft hover:bg-surface-3 hover:text-ink' }}">
                        <svg class="h-[18px] w-[18px] flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        {{-- Footer: user + tema + logout --}}
        <div class="border-t border-line p-3">
            <div class="flex items-center gap-2.5 px-1 pb-2">
                <div class="grid h-9 w-9 flex-none place-items-center rounded-full bg-surface-3 text-xs font-bold text-ink-soft">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-ink-faint">{{ $isAdmin ? 'Admin' : 'Kasir' }}</div>
                </div>
                <button type="button" onclick="ginelaToggleDark()" aria-label="Mode gelap" class="grid h-8 w-8 flex-none place-items-center rounded-lg text-ink-faint transition hover:bg-surface-3 hover:text-ink">
                    <svg class="h-[18px] w-[18px] dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="hidden h-[18px] w-[18px] dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-ink-soft transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Konten --}}
    <main class="flex min-w-0 flex-1 flex-col">
        {{ $slot }}
    </main>

    {{-- Bottom nav + drawer "Lainnya" — HP --}}
    <div x-data="{ open: false }" class="md:hidden">
        <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-40 bg-black/40"></div>
        <div x-show="open" x-cloak x-transition.origin.bottom
             class="fixed inset-x-0 bottom-16 z-50 rounded-t-2xl border-t border-line bg-surface p-3 shadow-pop">
            <div class="mb-1 px-1 text-[10px] font-bold uppercase tracking-wider text-ink-faint">Menu lainnya</div>
            <div class="grid grid-cols-3 gap-2">
                @php
                    $more = [
                        ['/transaksi', 'Riwayat', 'M9 2v4M15 2v4M4 5h16v17H4zM8 11h8M8 15h5'],
                        ['/laporan', 'Laporan', 'M3 3v18h18M7 14l3-3 3 2 4-5'],
                        ['/pengaturan', 'Toko', 'M12 9a3 3 0 100 6 3 3 0 000-6z'],
                    ];
                    if ($isAdmin) $more[] = ['/pengguna', 'Pengguna', 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8'];
                @endphp
                @foreach ($more as [$url, $label, $icon])
                    <a href="{{ $url }}" class="flex flex-col items-center gap-1.5 rounded-xl border border-line px-2 py-3 text-xs font-semibold {{ $isActive($url) ? 'chip-accent' : 'text-ink-soft' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" onclick="ginelaToggleDark()" class="flex flex-col items-center gap-1.5 rounded-xl border border-line px-2 py-3 text-xs font-semibold text-ink-soft">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    Mode
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full flex-col items-center gap-1.5 rounded-xl border border-line px-2 py-3 text-xs font-semibold text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <nav class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-stretch justify-around border-t border-line bg-surface">
            @foreach ($mobile as [$url, $label, $icon])
                @php $active = $isActive($url); @endphp
                <a href="{{ $url }}" class="flex flex-1 flex-col items-center justify-center gap-1 text-[10px] font-semibold {{ $active ? 'text-accent-600 dark:text-accent-300' : 'text-ink-faint' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $icon }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
            <button type="button" @click="open = !open" class="flex flex-1 flex-col items-center justify-center gap-1 text-[10px] font-semibold text-ink-faint">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                Lainnya
            </button>
        </nav>
    </div>
</div>

{{-- Toast --}}
<div x-data="{ items: [], push(d) { d = d || {}; const id = Date.now() + Math.random(); this.items.push({ id, msg: d.message || d.msg || '', type: d.type || 'success' }); setTimeout(() => { this.items = this.items.filter(t => t.id !== id); }, d.timeout || 2600); } }"
     @toast.window="push($event.detail)"
     class="pointer-events-none fixed bottom-20 right-4 z-[60] flex flex-col items-end gap-2 md:bottom-4">
    <template x-for="t in items" :key="t.id">
        <div class="animate-toast-in pointer-events-auto flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-black/10"
             :class="t.type === 'error' ? 'bg-red-600' : (t.type === 'info' ? 'bg-accent-600' : 'bg-emerald-600')">
            <svg x-show="t.type !== 'error'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            <svg x-show="t.type === 'error'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v5M12 16v.5M12 3l9 16H3z"/></svg>
            <span x-text="t.msg"></span>
        </div>
    </template>
</div>
</body>
</html>
