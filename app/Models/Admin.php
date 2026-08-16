<?php

namespace App\Models;

use App\Enums\AdminRole;
use App\Notifications\AdminResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
            'role' => AdminRole::class,
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AdminResetPassword($token));
    }

    public function ingredientStockMovements(): HasMany
    {
        return $this->hasMany(IngredientStockMovement::class, 'created_by');
    }
}
