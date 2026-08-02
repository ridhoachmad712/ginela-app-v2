<!DOCTYPE html>
@php $store = \App\Models\StoreSetting::current(); @endphp
<html lang="id" class="h-full" data-accent="{{ $store->theme_color ?? 'blue' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Ginela POS' }}</title>
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
<body class="h-full bg-base text-ink antialiased">
@php
    $nav = [
        ['url' => '/dashboard', 'label' => 'Beranda', 'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['url' => '/kasir', 'label' => 'Kasir', 'icon' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z'],
        ['url' => '/produk', 'label' => 'Produk', 'icon' => 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7'],
        ['url' => '/stok', 'label' => 'Stok', 'icon' => 'M4 7h16M4 12h16M4 17h16'],
        ['url' => '/transaksi', 'label' => 'Riwayat', 'icon' => 'M9 2v4M15 2v4M4 5h16v17H4zM8 11h8M8 15h5'],
        ['url' => '/laporan', 'label' => 'Laporan', 'icon' => 'M3 3v18h18M7 14l3-3 3 2 4-5'],
        ['url' => '/pengaturan', 'label' => 'Atur', 'icon' => 'M12 9a3 3 0 100 6 3 3 0 000-6z'],
    ];
    $path = request()->path();
@endphp

<div class="flex h-[100dvh] overflow-hidden">
    {{-- Rail — tablet & desktop --}}
    <nav class="hidden w-[84px] flex-none flex-col items-center gap-1 border-r border-line bg-surface py-4 md:flex">
        <div class="mb-3 grid h-11 w-11 place-items-center overflow-hidden rounded-2xl bg-accent-600 text-lg font-extrabold text-white">
            @if ($store->logo_path)<img src="{{ asset('storage/' . $store->logo_path) }}" class="h-full w-full bg-surface object-contain">@else G @endif
        </div>
        @foreach ($nav as $n)
            @php $active = str_starts_with('/'.$path, $n['url']) || ($n['url']==='/kasir' && $path==='/'); @endphp
            <a href="{{ $n['url'] }}"
               class="flex w-14 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-semibold transition {{ $active ? 'chip-accent' : 'text-ink-faint hover:bg-surface-3' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $n['icon'] }}"/></svg>
                {{ $n['label'] }}
            </a>
        @endforeach
        <div class="flex-1"></div>

        {{-- Theme toggle --}}
        <button type="button" onclick="ginelaToggleDark()" aria-label="Ganti mode gelap"
                class="grid h-10 w-10 place-items-center rounded-2xl text-ink-faint transition hover:bg-surface-3 hover:text-ink">
            <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>

        <div class="mt-1 grid h-10 w-10 place-items-center rounded-full bg-surface-3 text-sm font-bold text-ink-soft" title="{{ auth()->user()->name ?? '' }}">
            {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 2)) }}
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" aria-label="Keluar"
                    class="mt-1 grid h-10 w-10 place-items-center rounded-2xl text-ink-faint transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/15">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </button>
        </form>
    </nav>

    {{-- Konten --}}
    <main class="flex min-w-0 flex-1 flex-col">
        {{ $slot }}
    </main>

    {{-- Bottom nav — HP --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-stretch justify-around border-t border-line bg-surface md:hidden">
        @foreach (array_slice($nav, 0, 4) as $n)
            @php $active = str_starts_with('/'.$path, $n['url']) || ($n['url']==='/kasir' && $path==='/'); @endphp
            <a href="{{ $n['url'] }}" class="flex flex-1 flex-col items-center justify-center gap-1 text-[10px] font-semibold {{ $active ? 'text-accent-600 dark:text-accent-300' : 'text-ink-faint' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $n['icon'] }}"/></svg>
                {{ $n['label'] }}
            </a>
        @endforeach
        <button type="button" onclick="ginelaToggleDark()" class="flex flex-1 flex-col items-center justify-center gap-1 text-[10px] font-semibold text-ink-faint">
            <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            Mode
        </button>
        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <button type="submit" class="flex h-full w-full flex-col items-center justify-center gap-1 text-[10px] font-semibold text-ink-faint">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Keluar
            </button>
        </form>
    </nav>
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
