<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-extrabold tracking-tight">Masuk</h2>
    <p class="mb-6 text-sm text-slate-500">Selamat datang kembali 👋 Masuk untuk mulai bertransaksi.</p>

    <form wire:submit="login" class="flex flex-col gap-4">
        <label class="flex flex-col gap-1.5">
            <span class="text-xs font-semibold text-slate-500">Email</span>
            <input wire:model="form.email" type="email" name="email" required autofocus autocomplete="username"
                   placeholder="nama@ginela.local"
                   class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            @error('form.email')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="flex flex-col gap-1.5">
            <span class="text-xs font-semibold text-slate-500">Kata sandi</span>
            <input wire:model="form.password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            @error('form.password')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-slate-500">
                <input wire:model="form.remember" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="font-semibold text-blue-600 hover:underline">Lupa sandi?</a>
            @endif
        </div>

        <button type="submit" class="mt-1 flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 text-base font-bold text-white transition hover:bg-blue-700">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses…</span>
        </button>
    </form>

    <p class="mt-6 rounded-xl bg-slate-100 px-3 py-2 text-center text-xs text-slate-500">
        Demo · Admin <b>ridho@ginela.local</b> / <b>admin123</b>
    </p>
</div>
