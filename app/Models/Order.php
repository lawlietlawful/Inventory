<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /**
     * Centralized canteen sales table
     */
    protected $table = 'canteen.sales';

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * 'total' is a PostgreSQL GENERATED column (subtotal - discount)
     * Never insert or update 'total' directly.
     */
    protected $fillable = [
        'receipt_no',
        'shift_id',
        'cashier_id',
        'order_type',
        'subtotal',
        'discount',
        'status',
        'notes',
        'sold_at',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'discount'  => 'decimal:2',
        'total'     => 'decimal:2',
        'sold_at'   => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * Backward-compatible order_id accessor
     */
    public function getOrderIdAttribute(): int
    {
        return $this->id;
    }

    /**
     * Backward-compatible total_amount accessor
     */
    public function getTotalAmountAttribute()
    {
        return $this->total ?? ($this->subtotal - $this->discount);
    }

    /**
     * Backward-compatible order_date accessor
     */
    public function getOrderDateAttribute()
    {
        return $this->sold_at;
    }

    /**
     * Backward-compatible customer_name accessor
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->notes ?: ($this->order_type === 'dine_in' ? 'Dine In' : 'Take Out');
    }

    /**
     * Sale belongs to Cashier (canteen.users)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id', 'id');
    }

    /**
     * Sale belongs to Shift (canteen.shifts)
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    /**
     * Sale has many Sale Items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'sale_id', 'id');
    }

    /**
     * Sale has one Payment
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'sale_id', 'id');
    }
}
