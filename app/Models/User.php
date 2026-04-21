<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    // ========================================
    // СВЯЗИ
    // ========================================

    // Диалоги в которых участвует пользователь
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class);
    }

    // Все сообщения пользователя
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
