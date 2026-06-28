<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// ========================================
// AUTH - только для незалогиненных
// ========================================

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========================================
// ЧАТ - только для залогиненных
// ========================================

Route::middleware('auth')->group(function () {

    // запрос на вывод диалогов
    Route::get('/conversations/poll', [ConversationController::class, 'poll'])->name('conversations.poll');

    // Список диалогов
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');

    // Открыть диалог
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    // Начать диалог с пользователем
    Route::get('/conversations/start/{user}', [ConversationController::class, 'start'])->name('conversations.start');

    // Поиск пользователей (AJAX)
    Route::get('/users/search', [ConversationController::class, 'searchUsers'])->name('users.search');

    // Отправить сообщение (AJAX)
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');

    // Получить новые сообщения (AJAX polling)
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'fetch'])->name('messages.fetch');

});

// ========================================
// ГЛАВНАЯ
// ========================================

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('conversations.index');
    }
    return redirect()->route('login');
});
