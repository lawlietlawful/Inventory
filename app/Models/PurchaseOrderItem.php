<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'inventory.purchase_order_items';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity'  => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id', 'id');
    }

    public function getSubtotalAttribute(): float
    {
        return (float)($this->quantity * (float)$this->unit_cost);
    }
}
