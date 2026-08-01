<?php

namespace App\Livewire;

use App\Models\StoreSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class Pengaturan extends Component
{
    public bool $isAdmin = false;

    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $taxPct = '';

    public string $discPct = '';

    public string $pointPer1000 = '';

    public ?string $error = null;

    public bool $saved = false;

    public function mount(): void
    {
        $this->isAdmin = (bool) auth()->user()?->isAdmin();
        $s = StoreSetting::current();
        $this->name = $s->name;
        $this->address = $s->address;
        $this->phone = $s->phone;
        $this->taxPct = (string) round($s->tax_rate * 100);
        $this->discPct = (string) round($s->member_discount_rate * 100);
        $this->pointPer1000 = (string) round($s->point_per_rupiah * 1000);
    }

    public function save(): void
    {
        $this->error = null;
        $this->saved = false;
        if (! $this->isAdmin) {
            $this->error = 'Hanya admin yang boleh mengubah pengaturan.';

            return;
        }
        if (trim($this->name) === '') {
            $this->error = 'Nama toko wajib diisi.';

            return;
        }
        $pct = fn ($v) => is_numeric($v) && (float) $v >= 0 && (float) $v <= 100;
        if (! $pct($this->taxPct) || ! $pct($this->discPct)) {
            $this->error = 'Pajak & diskon harus 0–100%.';

            return;
        }
        if (! is_numeric($this->pointPer1000) || (float) $this->pointPer1000 < 0) {
            $this->error = 'Poin tidak valid.';

            return;
        }

        StoreSetting::current()->update([
            'name' => trim($this->name),
            'address' => trim($this->address),
            'phone' => trim($this->phone),
            'tax_rate' => (float) $this->taxPct / 100,
            'member_discount_rate' => (float) $this->discPct / 100,
            'point_per_rupiah' => (float) $this->pointPer1000 / 1000,
        ]);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.pengaturan');
    }
}
