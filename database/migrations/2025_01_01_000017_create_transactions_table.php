<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('channel')->default('OFFLINE'); // OFFLINE | ONLINE
            $t->foreignId('cashier_id')->constrained('users');
            $t->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $t->integer('subtotal');
            $t->integer('discount')->default(0);
            $t->integer('tax')->default(0);
            $t->integer('total');
            $t->integer('cost')->default(0);
            $t->integer('profit')->default(0);
            $t->integer('paid');
            $t->integer('change')->default(0);
            $t->string('method'); // CASH | QRIS | CARD
            $t->string('status')->default('COMPLETED');
            $t->timestamps();
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
