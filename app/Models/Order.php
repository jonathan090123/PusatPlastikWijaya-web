<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'voucher_id',
        'shipping_cost_id',
        'shipping_name',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'subtotal',
        'discount_amount',
        'points_used',
        'points_discount',
        'shipping_fee',
        'total',
        'status',
        'status_read_at',
        'admin_read_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'points_discount' => 'decimal:2',
            'shipping_fee'    => 'decimal:2',
            'total'           => 'decimal:2',
            'status_read_at'  => 'datetime',
            'admin_read_at'   => 'datetime',
        ];
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? (intval(substr($lastOrder->invoice_number, -4)) + 1) : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function shippingCost()
    {
        return $this->belongsTo(ShippingCost::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
                'pending'           => 'Menunggu',
                'waiting_payment'   => 'Menunggu Pembayaran',
                'paid'              => 'Sudah Dibayar',
                'processing'        => 'Diproses',
                'ready_for_pickup'  => 'Siap Diambil',
                'shipped'           => 'Dikirim',
                'completed'         => 'Selesai',
                'cancelled'         => 'Dibatalkan',
                'expired'           => 'Kadaluarsa',
                default             => $this->status,
            };
    }
}
