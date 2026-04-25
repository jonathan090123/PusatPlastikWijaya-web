<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',
        'unit',
        'quantity',
        'subtotal',
        'is_out_of_stock',
        'out_of_stock_at',
    ];

    protected function casts(): array
    {
        return [
            'product_price'   => 'decimal:2',
            'subtotal'        => 'decimal:2',
            'is_out_of_stock' => 'boolean',
            'out_of_stock_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
