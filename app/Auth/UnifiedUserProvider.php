<?php

namespace App\Auth;

use App\Models\InventoryUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class UnifiedUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $system = session('auth_system', 'canteen');

        if ($system === 'inventory') {
            return InventoryUser::find($identifier);
        }

        return User::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Remember token disabled
    }

    /**
     * Retrieve a user by the given credentials (searches canteen.users then inventory.users).
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) || !isset($credentials['username'])) {
            return null;
        }

        $username = trim($credentials['username']);

        // 1. Try Canteen Users first
        $canteenUser = User::where('username', $username)->first();
        if ($canteenUser) {
            session(['auth_system' => 'canteen']);
            return $canteenUser;
        }

        // 2. Try Inventory Users
        $inventoryUser = InventoryUser::where('username', $username)->first();
        if ($inventoryUser) {
            session(['auth_system' => 'inventory']);
            return $inventoryUser;
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? '';

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        return false;
    }
}
