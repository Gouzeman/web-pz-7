<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // Отправить сообщение (AJAX)
    public function store(Request $request, Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:51200', // макс. 50MB
        ]);

        if (empty($request->body) && !$request->hasFile('file')) {
            return response()->json(['error' => 'Сообщение пустое'], 422);
        }

        $filePath = null;
        $fileName = null;
        $fileType = null;

        // Если есть файл — сохраняем в storage/app/public/messages/
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('messages', 'public');
            $fileName = $file->getClientOriginalName();
            $fileType = Message::detectFileType($file->getClientOriginalExtension());
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => Auth::id(),
            'body'            => $request->body,
            'file_path'       => $filePath,
            'file_name'       => $fileName,
            'file_type'       => $fileType,
        ]);

        $message->load('user');

        return response()->json([
            'id'         => $message->id,
            'body'       => $message->body,
            'file_url'   => $filePath ? Storage::disk('public')->url($filePath) : null,
            'file_name'  => $message->file_name,
            'file_type'  => $message->file_type,
            'user_id'    => $message->user_id,
            'user_name'  => $message->user->name,
            'created_at' => $message->created_at->format('H:i'),
            'is_mine'    => true,
        ]);
    }

    // Получить новые сообщения после определённого id (AJAX polling)
    public function fetch(Request $request, Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        // Клиент передаёт id последнего известного сообщения
        $afterId = $request->input('after_id', 0);

        $messages = $conversation->messages()
            ->with('user')
            ->where('id', '>', $afterId)
            ->get()
            ->map(function (Message $msg) {
                return [
                    'id'         => $msg->id,
                    'body'       => $msg->body,
                    'file_url'   => $msg->file_path ? Storage::disk('public')->url($msg->file_path) : null,
                    'file_name'  => $msg->file_name,
                    'file_type'  => $msg->file_type,
                    'user_id'    => $msg->user_id,
                    'user_name'  => $msg->user->name,
                    'created_at' => $msg->created_at->format('H:i'),
                    'is_mine'    => $msg->user_id === Auth::id(),
                ];
            });

        // Помечаем полученные сообщения как прочитанные
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }
}
