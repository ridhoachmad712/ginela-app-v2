<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Ginela POS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
@php
    $nav = [
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
    <nav class="hidden w-[84px] flex-none flex-col items-center gap-1 border-r border-slate-200 bg-white py-4 md:flex">
        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-blue-600 text-lg font-extrabold text-white">G</div>
        @foreach ($nav as $n)
            @php $active = str_starts_with('/'.$path, $n['url']) || ($n['url']==='/kasir' && $path==='/'); @endphp
            <a href="{{ $n['url'] }}"
               class="flex w-14 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-semibold transition {{ $active ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:bg-slate-100' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $n['icon'] }}"/></svg>
                {{ $n['label'] }}
            </a>
        @endforeach
        <div class="flex-1"></div>
        <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-500" title="{{ auth()->user()->name ?? '' }}">
            {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 2)) }}
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" aria-label="Keluar"
                    class="mt-1 grid h-10 w-10 place-items-center rounded-2xl text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </button>
        </form>
    </nav>

    {{-- Konten --}}
    <main class="flex min-w-0 flex-1 flex-col">
        {{ $slot }}
    </main>

    {{-- Bottom nav — HP --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-stretch justify-around border-t border-slate-200 bg-white md:hidden">
        @foreach (array_slice($nav, 0, 4) as $n)
            @php $active = str_starts_with('/'.$path, $n['url']) || ($n['url']==='/kasir' && $path==='/'); @endphp
            <a href="{{ $n['url'] }}" class="flex flex-1 flex-col items-center justify-center gap-1 text-[10px] font-semibold {{ $active ? 'text-blue-600' : 'text-slate-400' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $n['icon'] }}"/></svg>
                {{ $n['label'] }}
            </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <button type="submit" class="flex h-full w-full flex-col items-center justify-center gap-1 text-[10px] font-semibold text-slate-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Keluar
            </button>
        </form>
    </nav>
</div>
</body>
</html>
