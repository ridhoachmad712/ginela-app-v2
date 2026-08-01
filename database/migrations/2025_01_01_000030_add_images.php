<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->string('image_path')->nullable()->after('emoji');
        });
        Schema::table('store_settings', function (Blueprint $t) {
            $t->string('logo_path')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('image_path'));
        Schema::table('store_settings', fn (Blueprint $t) => $t->dropColumn('logo_path'));
    }
};
