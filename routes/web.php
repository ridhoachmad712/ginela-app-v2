<?php

use App\Livewire\Kasir;
use App\Livewire\KelolaProduk;
use App\Livewire\Laporan;
use App\Livewire\Pengaturan;
use App\Livewire\Riwayat;
use App\Livewire\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/kasir');

Route::middleware(['auth'])->group(function () {
    Route::get('/kasir', Kasir::class)->name('kasir');
    Route::get('/produk', KelolaProduk::class)->name('produk');
    Route::get('/stok', Stok::class)->name('stok');
    Route::get('/transaksi', Riwayat::class)->name('transaksi');
    Route::get('/laporan', Laporan::class)->name('laporan');
    Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');
});

// Pasca-login Breeze mengarah ke 'dashboard' — alihkan ke kasir.
Route::redirect('/dashboard', '/kasir')->name('dashboard');

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

require __DIR__.'/auth.php';
