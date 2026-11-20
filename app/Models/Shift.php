<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'canteen.shifts';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'cashier_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'status',
    ];

    protected $casts = [
        'opened_at'    => 'datetime',
        'closed_at'    => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id', 'id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'shift_id', 'id');
    }
}
