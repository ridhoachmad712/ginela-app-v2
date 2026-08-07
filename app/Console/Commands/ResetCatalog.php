<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCatalog extends Command
{
    protected $signature = 'app:reset-catalog {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Kosongkan seluruh katalog: produk, varian, kategori, & transaksi terkait. Akun, pengaturan, member TETAP.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('HAPUS semua produk, varian, kategori, & transaksi? Tidak bisa dibatalkan.')) {
            return self::SUCCESS;
        }

        DB::transaction(function () {
            foreach ([
                'transaction_items', 'transactions', 'stock_movements',
                'variant_options', 'attribute_options', 'product_attributes',
                'product_variants', 'products', 'categories',
            ] as $t) {
                DB::table($t)->delete();
            }
        });

        $this->info('Katalog dikosongkan. Produk: '.DB::table('products')->count().' · Kategori: '.DB::table('categories')->count().' · Transaksi: '.DB::table('transactions')->count());

        return self::SUCCESS;
    }
}
