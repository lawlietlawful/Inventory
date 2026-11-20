<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'inventory.purchase_orders';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id', 'id');
    }

    public function getTotalAmountAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float)$this->items->sum(function ($item) {
                return (int)$item->quantity * (float)$item->unit_cost;
            });
        }
        return (float)$this->items()->selectRaw('SUM(quantity * unit_cost) as total')->value('total') ?? 0.0;
    }

    public function getTotalQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int)$this->items->sum('quantity');
        }
        return (int)$this->items()->sum('quantity');
    }
}
