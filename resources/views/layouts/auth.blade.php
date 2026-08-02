@php $store = \App\Models\StoreSetting::current(); @endphp
<!DOCTYPE html>
<html lang="id" class="h-full" data-accent="{{ $store->theme_color ?? 'blue' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ $store->name }}</title>
    <script>
        (function () {
            try {
                var d = localStorage.getItem('ginela-dark');
                if (d === '1' || (d === null && window.matchMedia('(prefers-color-scheme:dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-base text-ink antialiased">
<div class="flex min-h-[100dvh] flex-col md:flex-row">
    {{-- Panel brand --}}
    <div class="relative flex flex-col justify-center gap-8 overflow-hidden bg-gradient-to-br from-accent-600 to-sky-500 p-8 text-white md:w-1/2 md:p-12">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="relative flex items-center gap-3">
            <div class="grid h-14 w-14 flex-none place-items-center overflow-hidden rounded-2xl bg-white/15 text-2xl font-extrabold backdrop-blur">
                @if ($store->logo_path)<img src="{{ asset('storage/' . $store->logo_path) }}" class="h-full w-full bg-white object-contain">@else G @endif
            </div>
            <div class="text-xl font-bold tracking-tight">{{ $store->name }}</div>
        </div>
        <div class="relative">
            <h1 class="text-3xl font-extrabold leading-tight md:text-4xl">Kelola tokomu,<br>satu aplikasi.</h1>
            <p class="mt-3 max-w-sm text-white/80">Kasir, produk, stok, sampai laporan laba — cepat, rapi, dan siap dipakai transaksi nyata.</p>
        </div>
        <div class="relative flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/80">
            <span>🛒 Kasir cepat</span><span>📦 Varian &amp; harga ganda</span><span>📊 Laporan laba</span>
        </div>
    </div>

    {{-- Panel form --}}
    <div class="flex flex-1 items-center justify-center p-6">
        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
