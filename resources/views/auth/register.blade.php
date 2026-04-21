@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <main class="my-auto py-6 px-4">
        <div class="mx-auto max-w-[800px]">
            <div class="w-full bg-white flex flex-col p-[24px] border border-solid border-[#0000001a] rounded-[16px]">

                <h2 class="flex justify-center text-2xl font-semibold mb-4">Регистрация</h2>

                <form action="{{ route('register') }}" method="POST" class="flex flex-col gap-5 mb-4">
                    @csrf

                    {{-- Email --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Email</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Введите e-mail"
                            maxlength="50">
                    </div>

                    {{-- Имя --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Имя</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Введите имя"
                            maxlength="50">
                    </div>

                    {{-- Пароль --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Пароль</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="password"
                            name="password"
                            placeholder="Придумайте пароль"
                            maxlength="50">
                    </div>

                    {{-- Подтверждение пароля --}}
                    <div class="flex flex-col gap-2">
                        <label class="uppercase text-lg">Подтвердите пароль</label>
                        <input
                            class="border border-[#ccc] border-solid text-base p-[8px]"
                            type="password"
                            name="password_confirmation"
                            placeholder="Подтвердите пароль"
                            maxlength="50">
                        {{--
                            Важно: поле называется password_confirmation (не password_confirm).
                            Laravel автоматически проверяет совпадение с полем password
                            если в валидации указано правило 'confirmed'.
                        --}}
                    </div>

                    {{-- Кнопка --}}
                    <button type="submit" class="bg-[#1588e2] rounded-[10px] p-2 hover:opacity-[0.8] active:scale-[0.98]">
                        <span class="font-medium text-lg text-white">Зарегистрироваться</span>
                    </button>

                    <p>У вас уже есть учётная запись?
                        <a href="{{ route('login') }}" class="text-[#1588e2] hover:border-b-2">
                            Войти
                        </a>
                    </p>

                    {{-- Ошибки валидации --}}
                    @if($errors->any())
                        <div class="mt-2 p-3 bg-red-100 rounded-lg">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="text-red-700 text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </main>
@endsection
