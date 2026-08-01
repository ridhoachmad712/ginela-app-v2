<?php

namespace App\Livewire;

use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pos')]
class KelolaProduk extends Component
{
    public string $q = '';

    public ?string $mode = null; // 'new' | 'edit'

    public ?int $editId = null;

    public ?int $deletingId = null;

    public ?string $error = null;

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
    }

    #[Computed]
    public function products()
    {
        $s = trim(mb_strtolower($this->q));

        return Product::with(['category', 'attributes.options', 'variants'])
            ->where('is_active', true)
            ->when($s !== '', fn ($qq) => $qq->whereRaw('LOWER(name) like ?', ["%{$s}%"]))
            ->orderBy('name')->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('sort_order')->get();
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
        $this->reset(['fName', 'fCategory', 'fUnit', 'fEmoji', 'attrs', 'rows', 'editId', 'error']);
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
            if ((int) $r['offline'] <= 0 || (int) $r['online'] <= 0) {
                $this->error = 'Harga offline & online tiap varian harus lebih dari 0.';

                return;
            }
        }
        $pa = $this->parsedAttrs();

        DB::transaction(function () use ($pa) {
            $product = Product::create([
                'name' => trim($this->fName),
                'category_id' => $this->fCategory ? (int) $this->fCategory : null,
                'unit' => trim($this->fUnit) ?: 'pcs',
                'emoji' => trim($this->fEmoji) ?: null,
            ]);
            $optId = [];
            foreach ($pa as $ai => $a) {
                $attr = $product->attributes()->create(['name' => $a['name'], 'sort_order' => $ai]);
                foreach ($a['options'] as $oi => $val) {
                    $optId[$ai][$val] = $attr->options()->create(['value' => $val, 'sort_order' => $oi])->id;
                }
            }
            foreach ($this->rows as $r) {
                $v = $product->variants()->create([
                    'label' => implode(' / ', $r['combo']),
                    'offline_price' => (int) $r['offline'], 'online_price' => (int) $r['online'],
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
        DB::transaction(function () {
            Product::where('id', $this->editId)->update([
                'name' => trim($this->fName),
                'category_id' => $this->fCategory ? (int) $this->fCategory : null,
                'unit' => trim($this->fUnit) ?: 'pcs',
                'emoji' => trim($this->fEmoji) ?: null,
            ]);
            foreach ($this->editRows as $r) {
                \App\Models\ProductVariant::where('id', $r['id'])->update([
                    'offline_price' => (int) $r['offline'], 'online_price' => (int) $r['online'],
                    'cost_price' => (int) ($r['cost'] ?: 0), 'stock' => (int) ($r['stock'] ?: 0),
                    'min_stock' => (int) ($r['min'] ?: 5), 'is_active' => (bool) $r['active'],
                ]);
            }
        });
        $this->close();
    }

    public function deleteProduct(): void
    {
        if (! $this->guardAdmin() || ! $this->deletingId) {
            return;
        }
        Product::where('id', $this->deletingId)->update(['is_active' => false]);
        $this->deletingId = null;
        unset($this->products);
    }

    public function close(): void
    {
        $this->mode = null;
        $this->editId = null;
        unset($this->products);
    }

    public function render()
    {
        return view('livewire.kelola-produk');
    }
}
