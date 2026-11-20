<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Centralized table in canteen schema
     */
    protected $table = 'canteen.users';

    /**
     * Primary key
     */
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Backward-compatible idno attribute
     */
    public function getIdnoAttribute(): string
    {
        return 'CAN-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    public function getSystemAttribute(): string
    {
        return 'canteen';
    }

    public function isInventoryUser(): bool
    {
        return false;
    }

    public function isCanteenUser(): bool
    {
        return true;
    }

    /**
     * Disable remember token since canteen.users does not contain remember_token column
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Orders/Sales made by this cashier
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id', 'id');
    }
}
