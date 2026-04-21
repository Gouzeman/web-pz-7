<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Сообщения')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-[#efefef] min-h-screen flex flex-col">

{{-- Шапка — показываем только залогиненным --}}
@auth
    <nav class="bg-white border-b border-[#0000001a]">
        <div class="mx-auto max-w-[1200px] px-4 py-3 flex items-center justify-between">
            <a href="{{ route('conversations.index') }}"
               class="font-semibold text-lg text-[#1588e2]">
                Сообщения
            </a>
            <div class="flex items-center gap-4">
                <span class="text-gray-600 text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-red-500 hover:underline">Выйти</button>
                </form>
            </div>
        </div>
    </nav>
@endauth

{{-- Сообщение об ошибке --}}
@if(session('error'))
    <div class="mx-auto max-w-[1200px] w-full px-4 mt-4">
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    </div>
@endif

<main class="flex-1 flex flex-col">
    @yield('content')
</main>

</body>
</html>
