<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class InventoryUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'inventory.users';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    public function getIdnoAttribute(): string
    {
        return 'INV-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    public function getSystemAttribute(): string
    {
        return 'inventory';
    }

    public function isInventoryUser(): bool
    {
        return true;
    }

    public function isCanteenUser(): bool
    {
        return false;
    }
}
