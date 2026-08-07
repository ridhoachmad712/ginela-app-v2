<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProducts extends Command
{
    protected $signature = 'app:import-products {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Impor katalog + hierarki kategori dari database/data/products_import.json (mengganti seluruh katalog).';

    public function handle(): int
    {
        $path = base_path('database/data/products_import.json');
        if (! is_file($path)) {
            $this->error("File tidak ditemukan: $path");

            return self::FAILURE;
        }
        $data = json_decode(file_get_contents($path), true);
        $products = $data['products'] ?? null;
        if (! is_array($products)) {
            $this->error('JSON tidak valid.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Ini akan MENGHAPUS seluruh katalog & transaksi lama, lalu impor '.count($products).' produk. Lanjut?')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($products) {
            foreach (['transaction_items', 'transactions', 'stock_movements', 'variant_options', 'attribute_options', 'product_attributes', 'product_variants', 'products', 'categories'] as $t) {
                DB::table($t)->delete();
            }

            $topId = [];   // nama top -> id
            $subId = [];   // "top|sub" -> id
            $sort = 0;

            foreach ($products as $p) {
                $top = $p['top'];
                if (! isset($topId[$top])) {
                    $topId[$top] = Category::create([
                        'parent_id' => null, 'name' => $top, 'sort_order' => $sort++,
                        'shopee_admin_rate' => $p['admin_rate'] ?? 0, 'shopee_service_rate' => $p['service_rate'] ?? 0,
                    ])->id;
                }
                $catId = $topId[$top];
                if (! empty($p['sub'])) {
                    $key = $top.'|'.$p['sub'];
                    if (! isset($subId[$key])) {
                        $subId[$key] = Category::create([
                            'parent_id' => $topId[$top], 'name' => $p['sub'], 'sort_order' => $sort++,
                            'shopee_admin_rate' => 0, 'shopee_service_rate' => 0, // warisi dari induk
                        ])->id;
                    }
                    $catId = $subId[$key];
                }

                $product = Product::create([
                    'name' => $p['name'], 'category_id' => $catId, 'unit' => 'pcs', 'is_active' => true,
                ]);
                $product->variants()->create([
                    'label' => '', 'offline_price' => (int) $p['offline'], 'online_price' => (int) $p['online'],
                    'cost_price' => (int) $p['modal'], 'stock' => 0, 'min_stock' => 5, 'is_active' => true,
                ]);
            }

            $this->line('  Kategori induk: '.count($topId).' · Sub-kategori: '.count($subId));
        });

        $this->info('Selesai. Kategori: '.Category::count().' · Produk: '.Product::count());

        return self::SUCCESS;
    }
}
