<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Centralized canteen menu items table
     */
    protected $table = 'canteen.menu_items';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'product_id',
        'name',
        'description',
        'price',
        'is_available',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Backward-compatible product_id accessor
     */
    public function getProductIdAttribute(): int
    {
        return $this->id;
    }

    /**
     * Backward-compatible product_name accessor
     */
    public function getProductNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Real-time stock from Group 2's inventory.products
     */
    public function getStockQtyAttribute(): int
    {
        if ($this->relationLoaded('inventoryProduct') && $this->inventoryProduct) {
            return (int)$this->inventoryProduct->quantity_on_hand;
        }

        $inv = $this->inventoryProduct;
        return $inv ? (int)$inv->quantity_on_hand : 999;
    }

    /**
     * Menu Item belongs to a Menu Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * Linked to Group 2's warehouse product
     */
    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id', 'id');
    }

    /**
     * Menu Item has many Sale Items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id', 'id');
    }
}
