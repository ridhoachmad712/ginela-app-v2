<?php

namespace App\Livewire;

use App\Models\StoreSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.pos')]
class Pengaturan extends Component
{
    use WithFileUploads;

    public bool $isAdmin = false;

    public $logo = null;

    public ?string $existingLogo = null;

    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $taxPct = '';

    public string $discPct = '';

    public string $pointPer1000 = '';

    public string $themeColor = 'blue';

    public const THEMES = ['blue', 'green', 'violet', 'rose', 'orange', 'teal'];

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
        $this->existingLogo = $s->logo_path;
        $this->themeColor = in_array($s->theme_color, self::THEMES, true) ? $s->theme_color : 'blue';
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

        if ($this->logo) {
            $this->validate(['logo' => 'image|max:1024']);
        }
        $data = [
            'name' => trim($this->name),
            'address' => trim($this->address),
            'phone' => trim($this->phone),
            'tax_rate' => (float) $this->taxPct / 100,
            'member_discount_rate' => (float) $this->discPct / 100,
            'point_per_rupiah' => (float) $this->pointPer1000 / 1000,
            'theme_color' => in_array($this->themeColor, self::THEMES, true) ? $this->themeColor : 'blue',
        ];
        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('logo', 'public');
            $this->existingLogo = $data['logo_path'];
            $this->logo = null;
        }
        StoreSetting::current()->update($data);

        $this->saved = true;
        $this->dispatch('toast', message: 'Pengaturan tersimpan', type: 'success');
    }

    public function render()
    {
        return view('livewire.pengaturan');
    }
}
