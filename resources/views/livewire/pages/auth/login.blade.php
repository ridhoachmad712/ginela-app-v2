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
    <p class="mb-6 text-sm text-ink-soft">Pilih peran, lalu masukkan kata sandi.</p>

    <form wire:submit="login" class="flex flex-col gap-4">
        {{-- Pilih peran --}}
        <div class="flex flex-col gap-1.5">
            <span class="text-xs font-semibold text-ink-soft">Masuk sebagai</span>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" wire:click="$set('form.role', 'ADMIN')"
                        class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-4 text-sm font-bold transition {{ $form->role === 'ADMIN' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft hover:bg-surface-3' }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
                    Admin
                </button>
                <button type="button" wire:click="$set('form.role', 'KASIR')"
                        class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-4 text-sm font-bold transition {{ $form->role === 'KASIR' ? 'border-accent-600 bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300' : 'border-line text-ink-soft hover:bg-surface-3' }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 14h4"/></svg>
                    Kasir
                </button>
            </div>
            @error('form.role')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        </div>

        {{-- Kata sandi --}}
        <label class="flex flex-col gap-1.5">
            <span class="text-xs font-semibold text-ink-soft">Kata sandi {{ $form->role === 'ADMIN' ? 'Admin' : 'Kasir' }}</span>
            <input wire:model="form.password" type="password" name="password" required autofocus autocomplete="current-password"
                   placeholder="••••••••"
                   class="h-11 rounded-xl border border-line bg-surface px-3 text-sm outline-none transition focus:border-accent-500 focus:ring-2 focus:ring-accent-100">
            @error('form.password')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
            <input wire:model="form.remember" type="checkbox" class="rounded border-line text-accent-600 focus:ring-accent-500">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="mt-1 flex h-12 items-center justify-center gap-2 rounded-xl bg-accent-600 text-base font-bold text-white transition hover:bg-accent-700">
            <span wire:loading.remove wire:target="login">Masuk sebagai {{ $form->role === 'ADMIN' ? 'Admin' : 'Kasir' }}</span>
            <span wire:loading wire:target="login">Memproses…</span>
        </button>
    </form>
</div>
