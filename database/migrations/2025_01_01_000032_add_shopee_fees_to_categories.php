<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('shopee_admin_rate', 6, 4)->default(0)->after('name');
            $table->decimal('shopee_service_rate', 6, 4)->default(0)->after('shopee_admin_rate');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['shopee_admin_rate', 'shopee_service_rate']);
        });
    }
};
