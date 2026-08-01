<?php

namespace App\Services;

use App\Models\Member;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * @param  array<int,array>  $cart  keyed by variant id, tiap item punya 'qty'
     * @return array{ok:bool, error?:string, receipt?:array}
     */
    public function checkout(array $cart, string $channel, ?int $memberId, string $method, int $paid): array
    {
        if (empty($cart)) {
            return ['ok' => false, 'error' => 'Keranjang kosong.'];
        }

        $variants = ProductVariant::with('product')->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $s = StoreSetting::current();
        $priceOf = fn ($v) => $channel === 'ONLINE' ? $v->online_price : $v->offline_price;

        // Server = otoritas: hitung ulang dari harga DB.
        $subtotal = 0;
        $cost = 0;
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
        }

        $member = $memberId ? Member::find($memberId) : null;
        if ($memberId && ! $member) {
            return ['ok' => false, 'error' => 'Member tidak ditemukan.'];
        }
        $pointsBefore = $member?->points ?? 0;

        $hasMember = (bool) $member;
        $discount = $hasMember ? (int) round($subtotal * $s->member_discount_rate) : 0;
        $after = $subtotal - $discount;
        $tax = (int) round($after * $s->tax_rate);
        $total = $after + $tax;
        $profit = $after - $cost;
        $pointsEarned = $hasMember ? (int) floor($after * $s->point_per_rupiah) : 0;

        if ($method === 'CASH' && $paid < $total) {
            return ['ok' => false, 'error' => 'Nominal tunai kurang dari total.'];
        }
        $paidFinal = $method === 'CASH' ? $paid : $total;
        $change = $method === 'CASH' ? $paidFinal - $total : 0;

        $cashier = Auth::user();
        if (! $cashier) {
            return ['ok' => false, 'error' => 'Sesi berakhir. Silakan login ulang.'];
        }

        $trx = DB::transaction(function () use ($cart, $variants, $priceOf, $channel, $member, $cashier, $subtotal, $discount, $tax, $total, $cost, $profit, $paidFinal, $change, $method, $pointsEarned) {
            $now = now();
            $seq = Transaction::whereDate('created_at', $now->toDateString())->count() + 1;
            $code = 'TRX-'.$now->format('Ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $t = Transaction::create([
                'code' => $code, 'channel' => $channel, 'cashier_id' => $cashier->id,
                'member_id' => $member?->id, 'subtotal' => $subtotal, 'discount' => $discount,
                'tax' => $tax, 'total' => $total, 'cost' => $cost, 'profit' => $profit,
                'paid' => $paidFinal, 'change' => $change, 'method' => $method,
            ]);

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

            if ($member && $pointsEarned > 0) {
                $member->increment('points', $pointsEarned);
            }

            return $t;
        });

        $lines = [];
        foreach ($cart as $vid => $line) {
            $v = $variants[$vid];
            $lines[] = [
                'name' => $v->product->name.($v->label ? " ({$v->label})" : ''),
                'price' => $priceOf($v), 'qty' => (int) $line['qty'],
            ];
        }

        return ['ok' => true, 'receipt' => [
            'code' => $trx->code, 'at' => $trx->created_at->timestamp,
            'method' => $method, 'channel' => $channel, 'cashier' => $cashier->name,
            'member' => $member ? ['name' => $member->name, 'points' => $pointsBefore] : null,
            'lines' => $lines, 'subtotal' => $subtotal, 'discount' => $discount,
            'tax' => $tax, 'total' => $total, 'paid' => $paidFinal, 'change' => $change,
            'pointsEarned' => $pointsEarned,
        ]];
    }
}
