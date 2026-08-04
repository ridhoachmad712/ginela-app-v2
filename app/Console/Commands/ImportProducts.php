<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProducts extends Command
{
    protected $signature = 'app:import-products {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Impor katalog produk dari database/data/products_import.json (mengganti seluruh katalog).';

    public function handle(): int
    {
        $path = base_path('database/data/products_import.json');
        if (! is_file($path)) {
            $this->error("File tidak ditemukan: $path");

            return self::FAILURE;
        }
        $groups = json_decode(file_get_contents($path), true);
        if (! is_array($groups)) {
            $this->error('JSON tidak valid.');

            return self::FAILURE;
        }
        $totalProducts = array_sum(array_map(fn ($g) => count($g['products'] ?? []), $groups));

        if (! $this->option('force') && ! $this->confirm("Ini akan MENGHAPUS seluruh katalog & transaksi lama, lalu impor {$totalProducts} produk. Lanjut?")) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($groups) {
            // Hapus data lama (anak dulu, agar aman FK)
            foreach (['transaction_items', 'transactions', 'stock_movements', 'variant_options', 'attribute_options', 'product_attributes', 'product_variants', 'products', 'categories'] as $t) {
                DB::table($t)->delete();
            }

            $sort = 0;
            foreach ($groups as $g) {
                $cat = Category::create([
                    'name' => $g['category'],
                    'sort_order' => $sort++,
                    'shopee_admin_rate' => $g['admin_rate'] ?? 0,
                    'shopee_service_rate' => $g['service_rate'] ?? 0,
                ]);
                foreach ($g['products'] as $p) {
                    $product = Product::create([
                        'name' => $p['name'],
                        'category_id' => $cat->id,
                        'unit' => 'pcs',
                        'is_active' => true,
                    ]);
                    $product->variants()->create([
                        'label' => '',
                        'offline_price' => (int) $p['offline'],
                        'online_price' => (int) $p['online'],
                        'cost_price' => (int) $p['modal'],
                        'stock' => 0,
                        'min_stock' => 5,
                        'is_active' => true,
                    ]);
                }
                $this->line("  {$g['category']}: ".count($g['products']).' produk');
            }
        });

        $this->info('Selesai. Kategori: '.Category::count().' · Produk: '.Product::count());

        return self::SUCCESS;
    }
}
