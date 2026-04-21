<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ========================================
    // РЕГИСТРАЦИЯ
    // ========================================

    // Показать страницу регистрации (GET запрос)
    public function showRegister()
    {
        return view('auth.register');
    }

    // Обработать форму регистрации (POST запрос)
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        // Создаём пользователя в БД
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),

        ]);

        // Авторизуем пользователя сразу после регистрации
        Auth::login($user);
        return redirect()->route('conversations.index');
    }

    // ========================================
    // АВТОРИЗАЦИЯ
    // ========================================

    // Показать страницу логина (GET запрос)
    public function showLogin()
    {
        return view('auth.login');
    }

    // Обработать форму логина (POST запрос)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // защита от атаки Session Fixation
            $request->session()->regenerate();

            return redirect()->route('conversations.index');
        }

        // Если данные неверные — возвращаем назад с ошибкой.
        // withErrors() кладёт ошибку в $errors в шаблоне.
        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ]);
    }

    // ========================================
    // ВЫХОД
    // ========================================

    public function logout(Request $request)
    {
        Auth::logout();

        // Очищаем сессию и генерируем новый токен
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
