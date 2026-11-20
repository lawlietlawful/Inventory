<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Centralized canteen payments table
     */
    protected $table = 'canteen.payments';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'sale_id',
        'method',
        'amount_tendered',
        'change_given',
        'reference_no',
        'paid_at',
    ];

    protected $casts = [
        'amount_tendered' => 'decimal:2',
        'change_given'    => 'decimal:2',
        'paid_at'         => 'datetime',
    ];

    /**
     * Backward-compatible payment_id accessor
     */
    public function getPaymentIdAttribute(): int
    {
        return $this->id;
    }

    /**
     * Backward-compatible payment_method accessor
     */
    public function getPaymentMethodAttribute(): string
    {
        return $this->method;
    }

    /**
     * Backward-compatible amount_paid accessor
     */
    public function getAmountPaidAttribute()
    {
        return $this->amount_tendered;
    }

    /**
     * Backward-compatible change_amount accessor
     */
    public function getChangeAmountAttribute()
    {
        return $this->change_given;
    }

    /**
     * Payment belongs to Sale (Order)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sale_id', 'id');
    }
}
