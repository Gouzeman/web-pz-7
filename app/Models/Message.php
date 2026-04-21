<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'file_path',
        'file_name',
        'file_type',
        'read_at',
    ];

    // read_at приводим к объекту Carbon (удобная работа с датами)
    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ========================================
    // СВЯЗИ
    // ========================================

    // Отправитель сообщения
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Диалог которому принадлежит сообщение
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // ========================================
    // МЕТОДЫ
    // ========================================

    // Определить тип файла по расширению
    public static function detectFileType(string $extension): string
    {
        $images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videos = ['mp4', 'avi', 'mov', 'mkv', 'webm'];

        if (in_array(strtolower($extension), $images)) return 'image';
        if (in_array(strtolower($extension), $videos)) return 'video';
        return 'document';
    }
}
