<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'price_adjustment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFormattedAdjustmentAttribute(): string
    {
        $symbol = Setting::get('currency_symbol', '$');
        $adjustment = (float) $this->price_adjustment;

        if ($adjustment > 0) {
            return '+' . $symbol . number_format($adjustment, 2);
        } elseif ($adjustment < 0) {
            return '-' . $symbol . number_format(abs($adjustment), 2);
        }

        return $symbol . '0.00';
    }
}
