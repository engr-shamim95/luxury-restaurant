<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image',
        'is_available',
        'has_variants',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_available' => 'boolean',
            'has_variants' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('price_adjustment');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        $symbol = Setting::get('currency_symbol', '$');
        return $symbol . number_format((float) $this->base_price, 2);
    }

    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image)) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }
            return asset('storage/' . ltrim($this->image, '/'));
        }

        // Return a beautiful placeholder instead of an empty string
        return 'https://placehold.co/600x400/f59e0b/ffffff?text=' . urlencode($this->name);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug) && ! empty($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}
