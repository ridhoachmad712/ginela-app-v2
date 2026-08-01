<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Ginela Store');
            $t->string('address')->default('');
            $t->string('phone')->default('');
            $t->double('tax_rate')->default(0.11);
            $t->double('member_discount_rate')->default(0.05);
            $t->double('point_per_rupiah')->default(0.001);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
