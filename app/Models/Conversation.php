<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    // ========================================
    // СВЯЗИ
    // ========================================

    // Участники диалога (many-to-many через conversation_user)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Все сообщения диалога
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // Последнее сообщение (для превью в списке диалогов)
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // ========================================
    // МЕТОДЫ
    // ========================================

    // Найти диалог между двумя пользователями или создать новый
    public static function findOrCreateBetween(int $userAId, int $userBId): self
    {
        $conversation = self::whereHas('users', fn($q) => $q->where('users.id', $userAId))
            ->whereHas('users', fn($q) => $q->where('users.id', $userBId))
            ->first();

        if (!$conversation) {
            $conversation = self::create();
            $conversation->users()->attach([$userAId, $userBId]);
        }

        return $conversation;
    }

    // Получить собеседника (не текущего пользователя)
    public function getOtherUser(int $currentUserId): ?User
    {
        return $this->users->firstWhere('id', '!=', $currentUserId);
    }
}
