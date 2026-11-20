<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryProduct extends Model
{
    use HasFactory;

    protected $table = 'inventory.products';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'unit',
        'cost_price',
        'selling_price',
        'quantity_on_hand',
        'reorder_level',
        'status',
    ];

    protected $casts = [
        'cost_price'       => 'decimal:2',
        'selling_price'    => 'decimal:2',
        'quantity_on_hand' => 'integer',
        'reorder_level'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id', 'id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'id')->orderBy('created_at', 'desc');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id', 'id');
    }

    public function isLowStock(): bool
    {
        return (int)$this->quantity_on_hand <= (int)$this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return (int)$this->quantity_on_hand <= 0;
    }

    public function getTotalValuationAttribute(): float
    {
        return (float)($this->quantity_on_hand * (float)$this->cost_price);
    }
}
