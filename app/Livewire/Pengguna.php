<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Pengguna extends Component
{
    public bool $isAdmin = false;

    public ?string $mode = null; // 'new' | 'edit'

    public ?int $editId = null;

    public string $fName = '';

    public string $fRole = 'KASIR';

    public string $fPassword = '';

    public bool $fActive = true;

    public ?int $deletingId = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->isAdmin = (bool) auth()->user()?->isAdmin();
        abort_unless($this->isAdmin, 403);
    }

    #[Computed]
    public function users()
    {
        return User::orderByRaw("role = 'ADMIN' desc")->orderBy('name')->get();
    }

    public function openNew(): void
    {
        $this->reset(['fName', 'fRole', 'fPassword', 'fActive', 'editId', 'error']);
        $this->fRole = 'KASIR';
        $this->fActive = true;
        $this->mode = 'new';
    }

    public function openEdit(int $id): void
    {
        $u = User::find($id);
        if (! $u) {
            return;
        }
        $this->editId = $id;
        $this->fName = $u->name;
        $this->fRole = $u->role;
        $this->fActive = (bool) $u->is_active;
        $this->fPassword = '';
        $this->error = null;
        $this->mode = 'edit';
    }

    public function save(): void
    {
        $this->error = null;
        $name = trim($this->fName);
        if ($name === '') {
            $this->error = 'Nama wajib diisi.';

            return;
        }
        if (! in_array($this->fRole, ['ADMIN', 'KASIR'], true)) {
            $this->error = 'Peran tidak valid.';

            return;
        }
        $needPass = $this->mode === 'new';
        if (($needPass || $this->fPassword !== '') && strlen($this->fPassword) < 6) {
            $this->error = 'Kata sandi minimal 6 karakter.';

            return;
        }

        if ($this->mode === 'edit') {
            $willBeActiveAdmin = $this->fRole === 'ADMIN' && $this->fActive;
            if (! $willBeActiveAdmin
                && User::where('role', 'ADMIN')->where('is_active', true)->where('id', '!=', $this->editId)->doesntExist()) {
                $this->error = 'Harus ada minimal satu Admin aktif.';

                return;
            }
        }

        if ($this->mode === 'new') {
            User::create([
                'name' => $name,
                'email' => Str::slug($name).'-'.Str::lower(Str::random(5)).'@ginela.local',
                'role' => $this->fRole,
                'is_active' => $this->fActive,
                'password' => Hash::make($this->fPassword),
            ]);
            $msg = 'Pengguna ditambahkan';
        } else {
            $u = User::find($this->editId);
            if (! $u) {
                return;
            }
            $u->name = $name;
            $u->role = $this->fRole;
            $u->is_active = $this->fActive;
            if ($this->fPassword !== '') {
                $u->password = Hash::make($this->fPassword);
            }
            $u->save();
            $msg = 'Pengguna diperbarui';
        }

        $this->close();
        unset($this->users);
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }
        $u = User::find($this->deletingId);
        $this->deletingId = null;
        if (! $u) {
            return;
        }
        if ($u->id === auth()->id()) {
            $this->error = 'Tidak bisa menghapus akun yang sedang dipakai.';

            return;
        }
        if ($u->role === 'ADMIN'
            && User::where('role', 'ADMIN')->where('is_active', true)->where('id', '!=', $u->id)->doesntExist()) {
            $this->error = 'Tidak bisa menghapus Admin aktif terakhir.';

            return;
        }
        try {
            $u->delete();
            $this->dispatch('toast', message: 'Pengguna dihapus', type: 'info');
        } catch (\Throwable $e) {
            $this->error = 'Pengguna tidak bisa dihapus karena punya riwayat transaksi. Nonaktifkan saja.';
        }
        unset($this->users);
    }

    public function close(): void
    {
        $this->mode = null;
        $this->editId = null;
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.pengguna');
    }
}
