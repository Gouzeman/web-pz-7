@extends('layouts.app')

@section('title', 'Авторизация')

@section('content')
    <main class="my-auto py-6 px-4">
        <div class="mx-auto max-w-[800px]">
            <div class="w-full bg-white flex flex-col p-[24px] border border-solid border-[#0000001a] rounded-[16px]">

                <h2 class="flex justify-center text-2xl font-semibold mb-4">Авторизация</h2>

                <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-5">
                    @csrf
                    {{-- Поле логина --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Email</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"  {{-- восстанавливает введённое значение если форма не прошла валидацию --}}
                            placeholder="Введите email"
                            maxlength="50">

                    </div>

                    {{-- Поле пароля --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Пароль</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="password"
                            name="password"
                            placeholder="Введите пароль"
                            maxlength="50">
                    </div>

                    {{-- Кнопка --}}
                    <button type="submit" class="bg-[#1588e2] rounded-[10px] p-2 mb-2 hover:opacity-[0.8] active:scale-[0.98]">
                        <span class="font-medium text-lg text-white">Войти</span>
                    </button>

                    <p>У вас нет учётной записи?
                        <a href="{{ route('register') }}" class="text-[#1588e2] hover:border-b-2">
                            Зарегистрироваться
                        </a>
                    </p>

                    {{-- Блок ошибок валидации --}}
                    @if($errors->any())
                        <div class="mt-2 p-3 bg-red-100 rounded-lg">
                            <p class="text-center text-red-700">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </main>
@endsection
