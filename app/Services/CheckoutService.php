<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Catat penjualan (Shopee/offline) yang sudah terjadi.
     * Laba online sudah dipotong fee Shopee per kategori. Tanggal boleh lampau.
     *
     * @param  array<int,array>  $cart  keyed by variant id, tiap item punya 'qty'
     * @return array{ok:bool, error?:string, code?:string}
     */
    public function record(array $cart, string $channel, string $method, ?string $dateStr): array
    {
        if (empty($cart)) {
            return ['ok' => false, 'error' => 'Belum ada item.'];
        }

        $variants = ProductVariant::with('product.category')->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $s = StoreSetting::current();
        $priceOf = fn ($v) => $channel === 'ONLINE' ? $v->online_price : $v->offline_price;

        $subtotal = 0;
        $cost = 0;
        $shopeeFee = 0;
        foreach ($cart as $vid => $line) {
            $v = $variants[$vid] ?? null;
            if (! $v) {
                return ['ok' => false, 'error' => 'Varian produk tidak ditemukan.'];
            }
            $qty = (int) $line['qty'];
            if ($qty <= 0) {
                return ['ok' => false, 'error' => 'Jumlah tidak valid.'];
            }
            if ($v->stock < $qty) {
                $nm = $v->product->name.($v->label ? " ({$v->label})" : '');

                return ['ok' => false, 'error' => "Stok {$nm} tidak cukup (sisa {$v->stock})."];
            }
            $subtotal += $priceOf($v) * $qty;
            $cost += $v->cost_price * $qty;
            if ($channel === 'ONLINE') {
                $rate = $v->product->category?->shopeeFeeRate() ?? 0.0;
                $shopeeFee += (int) round($v->online_price * $qty * $rate);
            }
        }

        $cashier = Auth::user();
        if (! $cashier) {
            return ['ok' => false, 'error' => 'Sesi berakhir. Silakan login ulang.'];
        }

        try {
            $date = $dateStr ? Carbon::parse($dateStr) : now();
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Tanggal tidak valid.'];
        }
        if ($date->toDateString() > now()->toDateString()) {
            return ['ok' => false, 'error' => 'Tanggal tidak boleh di masa depan.'];
        }
        $when = $date->copy()->setTimeFrom(now());

        $tax = (int) round($subtotal * $s->tax_rate);
        $total = $subtotal + $tax;
        $profit = $subtotal - $cost - $shopeeFee;

        $trx = DB::transaction(function () use ($cart, $variants, $priceOf, $channel, $method, $cashier, $when, $subtotal, $tax, $total, $cost, $profit) {
            $seq = Transaction::whereDate('created_at', $when->toDateString())->count() + 1;
            $code = 'TRX-'.$when->format('Ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $t = Transaction::create([
                'code' => $code, 'channel' => $channel, 'cashier_id' => $cashier->id, 'member_id' => null,
                'subtotal' => $subtotal, 'discount' => 0, 'tax' => $tax, 'total' => $total,
                'cost' => $cost, 'profit' => $profit, 'paid' => $total, 'change' => 0,
                'method' => $method, 'status' => 'COMPLETED',
            ]);
            // Backdate ke tanggal penjualan (tanpa mengubah updated_at via timestamps otomatis)
            Transaction::where('id', $t->id)->update(['created_at' => $when, 'updated_at' => $when]);

            foreach ($cart as $vid => $line) {
                $v = $variants[$vid];
                $qty = (int) $line['qty'];
                $nm = $v->product->name.($v->label ? " ({$v->label})" : '');
                $t->items()->create([
                    'variant_id' => $vid, 'name_snapshot' => $nm,
                    'unit_price' => $priceOf($v), 'cost_snapshot' => $v->cost_price,
                    'qty' => $qty, 'line_total' => $priceOf($v) * $qty,
                ]);
                $before = $v->stock;
                $v->decrement('stock', $qty);
                StockMovement::create([
                    'variant_id' => $vid, 'type' => 'SALE', 'qty' => -$qty,
                    'stock_before' => $before, 'stock_after' => $before - $qty,
                    'ref_code' => $code, 'user_id' => $cashier->id,
                ]);
            }

            return $t;
        });

        return ['ok' => true, 'code' => $trx->code];
    }
}
