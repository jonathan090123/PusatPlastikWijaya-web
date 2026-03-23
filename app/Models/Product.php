<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'product_code',
        'name',
        'slug',
        'unit',
        'description',
        'price',
        'discount_price',
        'weight',
        'stock',
        'stock_alert',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getEffectivePrice()
    {
        return $this->discount_price ?? $this->price;
    }

    public function hasDiscount(): bool
    {
        return !is_null($this->discount_price) && $this->discount_price < $this->price;
    }

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Dapatkan harga untuk satuan tertentu.
     * Jika satuan = satuan dasar, return harga produk.
     * Jika tidak, cari di product_units.
     */
    public function getPriceForUnit(?string $unit = null): float
    {
        if (!$unit || $unit === $this->unit) {
            return (float) $this->getEffectivePrice();
        }

        $productUnit = $this->productUnits()->where('unit', $unit)->first();

        return $productUnit ? (float) $productUnit->price : (float) $this->getEffectivePrice();
    }

    /**
     * Dapatkan semua satuan yang tersedia (termasuk satuan dasar).
     */
    public function getAvailableUnits(): array
    {
        $units = [['unit' => $this->unit, 'price' => (float) $this->getEffectivePrice(), 'conversion_value' => 1]];

        foreach ($this->productUnits as $pu) {
            $units[] = ['unit' => $pu->unit, 'price' => (float) $pu->price, 'conversion_value' => $pu->conversion_value];
        }

        return $units;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_alert;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_alert');
    }
}
