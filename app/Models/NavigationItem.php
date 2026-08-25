<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'navigation_menu_id',
        'label',
        'url',
        'page_id',
        'order',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'navigation_menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function getResolvedUrlAttribute(): string
    {
        if (! empty($this->url)) {
            return $this->url;
        }

        if ($this->page) {
            return url('/page/' . $this->page->slug);
        }

        return '#';
    }
}
