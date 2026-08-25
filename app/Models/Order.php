<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DELIVERY = 'delivery';

    public const ORDER_STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_PREPARING => 'Preparing',
        self::STATUS_READY => 'Ready for Pickup/Delivery',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING => 'Pending',
        self::PAYMENT_PAID => 'Paid',
        self::PAYMENT_FAILED => 'Failed',
        self::PAYMENT_REFUNDED => 'Refunded',
    ];

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_type',
        'delivery_address',
        'order_notes',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'payment_status',
        'order_status',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getOrderNumberAttribute(): string
    {
        return '#ORD-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getFormattedTotalAttribute(): string
    {
        $symbol = Setting::get('currency_symbol', '$');
        return $symbol . number_format((float) $this->total, 2);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('order_status', $status);
    }

    public function scopePaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }
}
