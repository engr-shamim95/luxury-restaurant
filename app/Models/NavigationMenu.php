<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class)->orderBy('order');
    }

    public static function getByLocation(string $location): ?self
    {
        return static::where('location', $location)
            ->with(['items.page'])
            ->first();
    }
}
