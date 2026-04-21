<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // Список всех диалогов текущего пользователя
    public function index()
    {
        $conversations = Auth::user()
            ->conversations()
            ->with(['users', 'latestMessage.user'])
            ->get()
            ->sortByDesc(fn($c) => optional($c->latestMessage)->created_at)
            ->values();

        return view('conversations.index', compact('conversations'));
    }

    // Открыть конкретный диалог
    public function show(Conversation $conversation)
    {
        // Проверяем что текущий пользователь — участник диалога
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Вы не участник этого диалога.');
        }

        // Загружаем сообщения с авторами
        $messages = $conversation->messages()->with('user')->get();

        // Помечаем сообщения собеседника как прочитанные
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $otherUser = $conversation->getOtherUser(Auth::id());

        return view('conversations.show', compact('conversation', 'messages', 'otherUser'));
    }

    // Начать диалог с пользователем или открыть существующий
    public function start(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('conversations.index')
                ->with('error', 'Нельзя написать самому себе.');
        }

        $conversation = Conversation::findOrCreateBetween(Auth::id(), $user->id);

        return redirect()->route('conversations.show', $conversation);
    }

    // AJAX: поиск пользователей для нового диалога
    public function searchUsers(Request $request)
    {
        $query = $request->input('q', '');

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
