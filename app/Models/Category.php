<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'canteen.menu_categories';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    /**
     * Backward-compatible aliases
     */
    public function getCategoryIdAttribute(): int
    {
        return $this->id;
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Category has many Menu Items (Products)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
