<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Centralized canteen sale items table
     */
    protected $table = 'canteen.sale_items';

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * 'line_total' is a PostgreSQL GENERATED column (unit_price * quantity)
     * Never insert or update 'line_total' directly.
     */
    protected $fillable = [
        'sale_id',
        'menu_item_id',
        'item_name',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity'   => 'integer',
    ];

    /**
     * Backward-compatible order_item_id accessor
     */
    public function getOrderItemIdAttribute(): int
    {
        return $this->id;
    }

    /**
     * Backward-compatible order_id accessor
     */
    public function getOrderIdAttribute(): int
    {
        return $this->sale_id;
    }

    /**
     * Backward-compatible product_id accessor
     */
    public function getProductIdAttribute(): int
    {
        return $this->menu_item_id;
    }

    /**
     * Sale item belongs to Menu Item (Product)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'menu_item_id', 'id');
    }

    /**
     * Sale item belongs to Sale (Order)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sale_id', 'id');
    }
}
