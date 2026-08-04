<?php

namespace App\Livewire;

use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.pos')]
class KelolaProduk extends Component
{
    use WithFileUploads;

    public string $q = '';

    public $photo = null;

    public ?string $existingImage = null;

    public ?string $selCat = null; // null = tampilkan grid kategori; id | 'all' | 'none'

    public string $selCatName = '';

    public ?string $mode = null; // 'new' | 'edit'

    public ?int $editId = null;

    public ?int $deletingId = null;

    public ?string $error = null;

    // Kelola kategori
    public bool $catModal = false;

    public string $catName = '';

    public ?int $catEditId = null;

    public ?int $catDeleting = null;

    public string $catAdmin = '0';

    public string $catService = '0';

    public ?string $catError = null;

    // Form info
    public string $fName = '';

    public string $fCategory = '';

    public string $fUnit = 'pcs';

    public string $fEmoji = '';

    /** @var array<int,array{name:string,opts:string}> */
    public array $attrs = [];

    /** Baris varian (create). */
    public array $rows = [];

    /** Baris varian (edit) — dengan id. */
    public array $editRows = [];

    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->isAdmin = (bool) auth()->user()?->isAdmin();
        if ($eid = (int) request()->integer('edit')) {
            $this->openEdit($eid);
        }
    }

    #[Computed]
    public function stats(): array
    {
        $variants = \App\Models\ProductVariant::whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get(['stock', 'cost_price', 'min_stock']);

        return [
            'products' => Product::where('is_active', true)->count(),
            'invValue' => (int) $variants->sum(fn ($v) => $v->stock * $v->cost_price),
            'lowStock' => $variants->filter(fn ($v) => $v->stock <= $v->min_stock)->count(),
        ];
    }

    #[Computed]
    public function products()
    {
        $s = trim(mb_strtolower($this->q));

        return Product::with(['category', 'attributes.options', 'variants'])
            ->where('is_active', true)
            ->when($this->selCat === 'none', fn ($qq) => $qq->whereNull('category_id'))
            ->when($this->selCat !== null && ! in_array($this->selCat, ['all', 'none'], true),
                fn ($qq) => $qq->where('category_id', (int) $this->selCat))
            ->when($s !== '', fn ($qq) => $qq->whereRaw('LOWER(name) like ?', ["%{$s}%"]))
            ->orderBy('name')->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')->get();
    }

    #[Computed]
    public function catCards(): array
    {
        $counts = Product::where('is_active', true)
            ->selectRaw('category_id, count(*) as c')->groupBy('category_id')->pluck('c', 'category_id');
        $total = (int) $counts->sum();
        $cards = [['id' => 'all', 'name' => 'Semua Produk', 'count' => $total, 'emoji' => '📦']];
        foreach ($this->categories as $c) {
            $cards[] = ['id' => (string) $c->id, 'name' => $c->name, 'count' => (int) ($counts[$c->id] ?? 0), 'emoji' => '🏷️'];
        }
        $none = (int) ($counts[null] ?? $counts[''] ?? 0);
        if ($none > 0) {
            $cards[] = ['id' => 'none', 'name' => 'Tanpa Kategori', 'count' => $none, 'emoji' => '❔'];
        }

        return $cards;
    }

    public function openCat(string $val, string $name): void
    {
        $this->selCat = $val;
        $this->selCatName = $name;
        $this->q = '';
        unset($this->products);
    }

    public function backCats(): void
    {
        $this->selCat = null;
        $this->q = '';
    }

    // ---------- Kelola Kategori ----------
    public function openCatModal(): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $this->resetCatForm();
        $this->catDeleting = null;
        $this->catModal = true;
    }

    public function closeCatModal(): void
    {
        $this->catModal = false;
        $this->resetCatForm();
        $this->catDeleting = null;
    }

    public function resetCatForm(): void
    {
        $this->catName = '';
        $this->catEditId = null;
        $this->catAdmin = '0';
        $this->catService = '0';
        $this->catError = null;
    }

    private function fmtPct($rate): string
    {
        return rtrim(rtrim(number_format((float) $rate * 100, 2, '.', ''), '0'), '.') ?: '0';
    }

    public function editCat(int $id): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $c = Category::find($id);
        if (! $c) {
            return;
        }
        $this->catEditId = $id;
        $this->catName = $c->name;
        $this->catAdmin = $this->fmtPct($c->shopee_admin_rate);
        $this->catService = $this->fmtPct($c->shopee_service_rate);
        $this->catError = null;
    }

    public function saveCat(): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $name = trim($this->catName);
        if ($name === '') {
            $this->catError = 'Nama kategori wajib diisi.';

            return;
        }
        $dupe = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($this->catEditId, fn ($q) => $q->where('id', '!=', $this->catEditId))
            ->exists();
        if ($dupe) {
            $this->catError = 'Kategori "'.$name.'" sudah ada.';

            return;
        }
        $pct = fn ($v) => is_numeric($v) && (float) $v >= 0 && (float) $v <= 100;
        if (! $pct($this->catAdmin) || ! $pct($this->catService)) {
            $this->catError = 'Fee admin & layanan harus 0–100%.';

            return;
        }
        $data = [
            'name' => $name,
            'shopee_admin_rate' => (float) $this->catAdmin / 100,
            'shopee_service_rate' => (float) $this->catService / 100,
        ];

        if ($this->catEditId) {
            Category::where('id', $this->catEditId)->update($data);
            $msg = 'Kategori diperbarui';
        } else {
            Category::create($data + ['sort_order' => (int) Category::max('sort_order') + 1]);
            $msg = 'Kategori ditambahkan';
        }
        $this->resetCatForm();
        unset($this->categories, $this->catCards, $this->products);
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function askDeleteCat(int $id): void
    {
        $this->catDeleting = $id;
        $this->catError = null;
    }

    public function cancelDeleteCat(): void
    {
        $this->catDeleting = null;
    }

    public function deleteCat(): void
    {
        if (! $this->guardAdmin() || ! $this->catDeleting) {
            return;
        }
        DB::transaction(function () {
            Product::where('category_id', $this->catDeleting)->update(['category_id' => null]);
            Category::where('id', $this->catDeleting)->delete();
        });
        if ($this->catEditId === $this->catDeleting) {
            $this->resetCatForm();
        }
        $this->catDeleting = null;
        unset($this->categories, $this->catCards, $this->products);
        $this->dispatch('toast', message: 'Kategori dihapus', type: 'info');
    }

    private function guardAdmin(): bool
    {
        if (! $this->isAdmin) {
            $this->error = 'Hanya admin yang boleh mengelola produk.';

            return false;
        }

        return true;
    }

    // ---------- Create ----------
    public function openNew(): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $this->reset(['fName', 'fCategory', 'fUnit', 'fEmoji', 'attrs', 'rows', 'editId', 'error', 'photo', 'existingImage']);
        $this->fUnit = 'pcs';
        $this->rows = [$this->emptyRow([])];
        $this->mode = 'new';
    }

    private function emptyRow(array $combo): array
    {
        return ['combo' => $combo, 'label' => implode(' / ', $combo), 'offline' => '', 'online' => '', 'cost' => '', 'stock' => '', 'min' => '5'];
    }

    public function addAttr(): void
    {
        $this->attrs[] = ['name' => '', 'opts' => ''];
    }

    public function removeAttr(int $i): void
    {
        unset($this->attrs[$i]);
        $this->attrs = array_values($this->attrs);
    }

    private function parsedAttrs(): array
    {
        $out = [];
        foreach ($this->attrs as $a) {
            $name = trim($a['name']);
            $opts = array_values(array_filter(array_map('trim', explode(',', $a['opts'])), fn ($x) => $x !== ''));
            if ($name !== '' && count($opts)) {
                $out[] = ['name' => $name, 'options' => $opts];
            }
        }

        return $out;
    }

    public function generateVariants(): void
    {
        $pa = $this->parsedAttrs();
        $combos = [[]];
        foreach ($pa as $a) {
            $next = [];
            foreach ($combos as $c) {
                foreach ($a['options'] as $v) {
                    $next[] = [...$c, $v];
                }
            }
            $combos = $next;
        }
        $old = collect($this->rows)->keyBy('label');
        $this->rows = array_map(function ($combo) use ($old) {
            $label = implode(' / ', $combo);

            return $old->get($label) ?? $this->emptyRow($combo);
        }, $combos);
    }

    public function saveNew(): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $this->error = null;
        if (trim($this->fName) === '') {
            $this->error = 'Nama produk wajib diisi.';

            return;
        }
        foreach ($this->rows as $r) {
            if ((int) $r['online'] <= 0) {
                $this->error = 'Harga online tiap varian harus lebih dari 0.';

                return;
            }
        }
        $pa = $this->parsedAttrs();
        if ($this->photo) {
            $this->validate(['photo' => 'image|max:2048']);
        }
        $imagePath = $this->photo ? $this->photo->store('products', 'public') : null;
        $offDisc = (float) \App\Models\StoreSetting::current()->offline_discount_rate;

        DB::transaction(function () use ($pa, $imagePath, $offDisc) {
            $product = Product::create([
                'name' => trim($this->fName),
                'category_id' => $this->fCategory ? (int) $this->fCategory : null,
                'unit' => trim($this->fUnit) ?: 'pcs',
                'emoji' => trim($this->fEmoji) ?: null,
                'image_path' => $imagePath,
            ]);
            $optId = [];
            foreach ($pa as $ai => $a) {
                $attr = $product->attributes()->create(['name' => $a['name'], 'sort_order' => $ai]);
                foreach ($a['options'] as $oi => $val) {
                    $optId[$ai][$val] = $attr->options()->create(['value' => $val, 'sort_order' => $oi])->id;
                }
            }
            foreach ($this->rows as $r) {
                $online = (int) $r['online'];
                $v = $product->variants()->create([
                    'label' => implode(' / ', $r['combo']),
                    'offline_price' => (int) round($online * (1 - $offDisc)), 'online_price' => $online,
                    'cost_price' => (int) ($r['cost'] ?: 0), 'stock' => (int) ($r['stock'] ?: 0),
                    'min_stock' => (int) ($r['min'] ?: 5),
                ]);
                $ids = [];
                foreach ($r['combo'] as $ai => $val) {
                    if (isset($optId[$ai][$val])) {
                        $ids[] = $optId[$ai][$val];
                    }
                }
                if ($ids) {
                    $v->options()->attach($ids);
                }
            }
        });

        $this->close();
        $this->dispatch('toast', message: 'Produk ditambahkan', type: 'success');
    }

    // ---------- Edit ----------
    public function openEdit(int $id): void
    {
        if (! $this->guardAdmin()) {
            return;
        }
        $p = Product::with('variants')->find($id);
        if (! $p) {
            return;
        }
        $this->editId = $id;
        $this->fName = $p->name;
        $this->fCategory = (string) ($p->category_id ?? '');
        $this->fUnit = $p->unit;
        $this->fEmoji = $p->emoji ?? '';
        $this->existingImage = $p->image_path;
        $this->photo = null;
        $this->editRows = $p->variants->map(fn ($v) => [
            'id' => $v->id, 'label' => $v->label,
            'offline' => (string) $v->offline_price, 'online' => (string) $v->online_price,
            'cost' => (string) $v->cost_price, 'stock' => (string) $v->stock,
            'min' => (string) $v->min_stock, 'active' => (bool) $v->is_active,
        ])->all();
        $this->error = null;
        $this->mode = 'edit';
    }

    public function saveEdit(): void
    {
        if (! $this->guardAdmin() || ! $this->editId) {
            return;
        }
        $this->error = null;
        if (trim($this->fName) === '') {
            $this->error = 'Nama produk wajib diisi.';

            return;
        }
        foreach ($this->editRows as $r) {
            if ((int) $r['online'] <= 0) {
                $this->error = 'Harga online tiap varian harus lebih dari 0.';

                return;
            }
        }
        if ($this->photo) {
            $this->validate(['photo' => 'image|max:2048']);
        }
        $newImage = $this->photo ? $this->photo->store('products', 'public') : null;
        $offDisc = (float) \App\Models\StoreSetting::current()->offline_discount_rate;

        DB::transaction(function () use ($newImage, $offDisc) {
            $data = [
                'name' => trim($this->fName),
                'category_id' => $this->fCategory ? (int) $this->fCategory : null,
                'unit' => trim($this->fUnit) ?: 'pcs',
                'emoji' => trim($this->fEmoji) ?: null,
            ];
            if ($newImage) {
                $data['image_path'] = $newImage;
            }
            Product::where('id', $this->editId)->update($data);
            foreach ($this->editRows as $r) {
                $online = (int) $r['online'];
                \App\Models\ProductVariant::where('id', $r['id'])->update([
                    'offline_price' => (int) round($online * (1 - $offDisc)), 'online_price' => $online,
                    'cost_price' => (int) ($r['cost'] ?: 0), 'stock' => (int) ($r['stock'] ?: 0),
                    'min_stock' => (int) ($r['min'] ?: 5), 'is_active' => (bool) $r['active'],
                ]);
            }
        });
        $this->close();
        $this->dispatch('toast', message: 'Perubahan produk tersimpan', type: 'success');
    }

    public function deleteProduct(): void
    {
        if (! $this->guardAdmin() || ! $this->deletingId) {
            return;
        }
        Product::where('id', $this->deletingId)->update(['is_active' => false]);
        $this->deletingId = null;
        unset($this->products);
        $this->dispatch('toast', message: 'Produk dinonaktifkan', type: 'info');
    }

    public function close(): void
    {
        $this->mode = null;
        $this->editId = null;
        unset($this->products);
    }

    public function render()
    {
        $feeMap = $this->categories->mapWithKeys(fn ($c) => [$c->id => round($c->shopeeFeeRate(), 4)])->all();
        $offlineDisc = (float) \App\Models\StoreSetting::current()->offline_discount_rate;

        return view('livewire.kelola-produk', compact('feeMap', 'offlineDisc'));
    }
}
