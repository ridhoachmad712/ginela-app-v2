<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Member;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catNames = ['Makanan', 'Minuman', 'Sembako', 'Perawatan', 'Fashion'];
        $cat = [];
        foreach ($catNames as $i => $n) {
            $cat[$n] = Category::create(['name' => $n, 'sort_order' => $i])->id;
        }

        // Produk sederhana (1 varian default, label kosong).
        $simple = [
            ['name' => 'Indomie Goreng', 'cat' => 'Makanan', 'em' => '🍜', 'off' => 3500, 'on' => 4000, 'cost' => 2800, 'stock' => 120],
            ['name' => 'Aqua Botol 600ml', 'cat' => 'Minuman', 'em' => '💧', 'off' => 4000, 'on' => 4500, 'cost' => 2900, 'stock' => 64],
            ['name' => 'Beras Ramos 5kg', 'cat' => 'Sembako', 'em' => '🍚', 'off' => 68000, 'on' => 72000, 'cost' => 61000, 'stock' => 18],
            ['name' => 'Minyak Bimoli 1L', 'cat' => 'Sembako', 'em' => '🛢️', 'off' => 21000, 'on' => 22500, 'cost' => 18500, 'stock' => 20],
            ['name' => 'Telur Ayam 1kg', 'cat' => 'Sembako', 'em' => '🥚', 'off' => 28000, 'on' => 30000, 'cost' => 24000, 'stock' => 26],
            ['name' => 'Gula Pasir 1kg', 'cat' => 'Sembako', 'em' => '🧂', 'off' => 16500, 'on' => 17500, 'cost' => 14500, 'stock' => 41],
            ['name' => 'Sabun Lifebuoy', 'cat' => 'Perawatan', 'em' => '🧼', 'off' => 4500, 'on' => 5000, 'cost' => 3300, 'stock' => 52],
            ['name' => 'Shampoo Clear 160ml', 'cat' => 'Perawatan', 'em' => '🧴', 'off' => 24000, 'on' => 26000, 'cost' => 19500, 'stock' => 15],
        ];
        foreach ($simple as $s) {
            $p = Product::create(['name' => $s['name'], 'category_id' => $cat[$s['cat']] ?? null, 'emoji' => $s['em']]);
            $p->variants()->create([
                'label' => '', 'offline_price' => $s['off'], 'online_price' => $s['on'],
                'cost_price' => $s['cost'], 'stock' => $s['stock'],
            ]);
        }

        // Produk bervarian (matriks atribut).
        $matrix = [
            [
                'name' => 'Kopi Susu Gula Aren', 'cat' => 'Minuman', 'em' => '☕',
                'attrs' => [
                    ['name' => 'Ukuran', 'options' => ['Reguler', 'Large']],
                    ['name' => 'Suhu', 'options' => ['Panas', 'Dingin']],
                ],
                'price' => function ($combo) {
                    $off = 18000;
                    if (in_array('Large', $combo)) $off += 5000;
                    if (in_array('Dingin', $combo)) $off += 2000;

                    return ['off' => $off, 'on' => $off + 3000, 'cost' => 8000, 'stock' => 40];
                },
            ],
            [
                'name' => 'Kaos Polos Cotton', 'cat' => 'Fashion', 'em' => '👕',
                'attrs' => [
                    ['name' => 'Ukuran', 'options' => ['S', 'M', 'L', 'XL']],
                    ['name' => 'Warna', 'options' => ['Hitam', 'Putih']],
                ],
                'price' => function ($combo) {
                    $off = 75000;
                    if (in_array('XL', $combo)) $off += 10000;

                    return ['off' => $off, 'on' => $off + 15000, 'cost' => 45000, 'stock' => 12];
                },
            ],
        ];
        foreach ($matrix as $m) {
            $product = Product::create(['name' => $m['name'], 'category_id' => $cat[$m['cat']] ?? null, 'emoji' => $m['em']]);
            $optId = [];
            foreach ($m['attrs'] as $ai => $a) {
                $attr = $product->attributes()->create(['name' => $a['name'], 'sort_order' => $ai]);
                foreach ($a['options'] as $oi => $val) {
                    $optId[$val] = $attr->options()->create(['value' => $val, 'sort_order' => $oi])->id;
                }
            }
            // Kombinasi kartesian.
            $combos = [[]];
            foreach ($m['attrs'] as $a) {
                $next = [];
                foreach ($combos as $c) {
                    foreach ($a['options'] as $val) {
                        $next[] = array_merge($c, [$val]);
                    }
                }
                $combos = $next;
            }
            foreach ($combos as $combo) {
                $pr = $m['price']($combo);
                $v = $product->variants()->create([
                    'label' => implode(' / ', $combo),
                    'offline_price' => $pr['off'], 'online_price' => $pr['on'],
                    'cost_price' => $pr['cost'], 'stock' => $pr['stock'],
                ]);
                $v->options()->attach(array_map(fn ($val) => $optId[$val], $combo));
            }
        }

        foreach ([
            ['name' => 'Siti Rahayu', 'phone' => '0812-1111-2222', 'points' => 340],
            ['name' => 'Budi Santoso', 'phone' => '0813-3333-4444', 'points' => 125],
            ['name' => 'Dewi Lestari', 'phone' => '0857-5555-6666', 'points' => 890],
            ['name' => 'Agus Pratama', 'phone' => '0821-7777-8888', 'points' => 55],
        ] as $mem) {
            Member::create($mem);
        }

        User::create(['name' => 'Ridho', 'email' => 'ridho@ginela.local', 'password' => Hash::make('admin123'), 'role' => 'ADMIN']);
        User::create(['name' => 'Kasir 1', 'email' => 'kasir@ginela.local', 'password' => Hash::make('kasir123'), 'role' => 'KASIR']);

        StoreSetting::create([
            'id' => 1, 'name' => 'Ginela Store',
            'address' => 'Jl. Merdeka No. 10, Bandung', 'phone' => '0812-3456-7890',
        ]);
    }
}
