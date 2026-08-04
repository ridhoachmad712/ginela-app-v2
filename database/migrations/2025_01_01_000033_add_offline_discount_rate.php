<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            // Selisih harga offline terhadap harga online (mis. 0.10 = offline 10% lebih murah)
            $table->decimal('offline_discount_rate', 6, 4)->default(0.10)->after('point_per_rupiah');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('offline_discount_rate');
        });
    }
};
