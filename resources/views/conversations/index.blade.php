@extends('layouts.app')

@section('title', 'Диалоги')

@section('content')
    <div class="max-w-[800px] w-full mx-auto px-4 py-6">

        {{-- Заголовок + кнопка нового диалога --}}
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">Диалоги</h1>
            <button onclick="toggleSearch()"
                    class="bg-[#1588e2] text-white px-4 py-2 rounded-[10px] text-sm hover:opacity-80 active:scale-[0.98]">
                + Новый диалог
            </button>
        </div>

        {{-- Поиск пользователя (скрыт по умолчанию) --}}
        <div id="user-search" class="hidden mb-4">
            <div class="bg-white border border-[#0000001a] rounded-[16px] p-4">
                <input type="text"
                       id="search-input"
                       placeholder="Поиск по имени или email..."
                       class="w-full border border-[#ccc] text-base p-[8px] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1588e2]">
                <div id="search-results" class="mt-2 space-y-1"></div>
            </div>
        </div>

        {{-- Список диалогов --}}
        <div id="conversations-list" class="bg-white border border-[#0000001a] rounded-[16px] overflow-hidden">
            @forelse($conversations as $conv)
                @php $other = $conv->getOtherUser(Auth::id()); @endphp
                @if($other)
                    <a href="{{ route('conversations.show', $conv) }}"
                       data-conversation-id="{{ $conv->id }}"
                       class="flex items-center gap-4 px-5 py-4 border-b border-[#0000001a] hover:bg-[#efefef] transition-colors last:border-b-0">

                        <div class="w-11 h-11 rounded-full bg-[#1588e2] flex items-center justify-center text-white font-semibold flex-shrink-0">
                            {{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <span class="font-medium text-gray-900">{{ $other->name }}</span>
                                @if($conv->latestMessage)
                                    <span class="conv-time text-xs text-gray-400 flex-shrink-0 ml-2">
                            {{ $conv->latestMessage->created_at->format('H:i') }}
                        </span>
                                @endif
                            </div>
                            @if($conv->latestMessage)
                                <p class="conv-preview text-sm text-gray-500 truncate mt-0.5">
                                    @if($conv->latestMessage->user_id === Auth::id())
                                        <span class="text-gray-400">Вы: </span>
                                    @endif
                                    {{ $conv->latestMessage->body ?? '📎 Файл' }}
                                </p>
                            @endif
                        </div>

                    </a>
                @endif
            @empty
                <p class="empty-placeholder text-center text-gray-400 py-12">
                    Нет диалогов. Нажмите «+ Новый диалог» чтобы начать.
                </p>
            @endforelse
        </div>

    </div>

    <script>
        // ========================================
        // ПОИСК ПОЛЬЗОВАТЕЛЕЙ
        // ========================================
        function toggleSearch() {
            document.getElementById('user-search').classList.toggle('hidden');
            if (!document.getElementById('user-search').classList.contains('hidden')) {
                document.getElementById('search-input').focus();
            }
        }

        let searchTimer;
        document.getElementById('search-input').addEventListener('input', function () {
            clearTimeout(searchTimer);
            const query = this.value.trim();
            const results = document.getElementById('search-results');

            if (query.length < 1) {
                results.innerHTML = '';
                return;
            }

            searchTimer = setTimeout(async () => {
                const response = await fetch(`/users/search?q=${encodeURIComponent(query)}`);
                const users = await response.json();

                results.innerHTML = users.length
                    ? users.map(u => `
                <a href="/conversations/start/${u.id}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#efefef] transition-colors">
                    <div class="w-8 h-8 rounded-full bg-[#1588e2] flex items-center justify-center text-white text-sm font-semibold">
                        ${u.name[0].toUpperCase()}
                    </div>
                    <div>
                        <div class="font-medium text-sm">${u.name}</div>
                        <div class="text-xs text-gray-400">${u.email}</div>
                    </div>
                </a>`).join('')
                    : '<p class="text-sm text-gray-400 text-center py-2">Не найдено</p>';
            }, 300);
        });

        // ========================================
        // POLLING — обновление списка диалогов
        // ========================================

        // Собираем id диалогов которые уже есть на странице
        function getExistingIds() {
            return [...document.querySelectorAll('[data-conversation-id]')]
                .map(el => parseInt(el.dataset.conversationId));
        }

        async function pollConversations() {
            try {
                const response = await fetch('/conversations/poll');
                const conversations = await response.json();

                conversations.forEach(conv => {
                    const existing = document.querySelector(`[data-conversation-id="${conv.id}"]`);

                    if (existing) {
                        // Диалог уже есть — обновляем превью
                        const preview = existing.querySelector('.conv-preview');
                        const time = existing.querySelector('.conv-time');
                        if (preview) preview.textContent = (conv.is_mine ? 'Вы: ' : '') + conv.last_body;
                        if (time) time.textContent = conv.last_time;
                    } else {
                        // Новый диалог — добавляем в начало списка
                        const list = document.getElementById('conversations-list');
                        const div = document.createElement('a');
                        div.href = `/conversations/${conv.id}`;
                        div.dataset.conversationId = conv.id;
                        div.className = 'flex items-center gap-4 px-5 py-4 border-b border-[#0000001a] hover:bg-[#efefef] transition-colors';
                        div.innerHTML = `
                    <div class="w-11 h-11 rounded-full bg-[#1588e2] flex items-center justify-center text-white font-semibold flex-shrink-0">
                        ${conv.other_name[0].toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <span class="font-medium text-gray-900">${conv.other_name}</span>
                            <span class="conv-time text-xs text-gray-400 flex-shrink-0 ml-2">${conv.last_time}</span>
                        </div>
                        <p class="conv-preview text-sm text-gray-500 truncate mt-0.5">
                            ${conv.is_mine ? 'Вы: ' : ''}${conv.last_body}
                        </p>
                    </div>`;
                        // Убираем заглушку "нет диалогов" если она есть
                        const empty = list.querySelector('.empty-placeholder');
                        if (empty) empty.remove();

                        list.prepend(div); // добавляем в начало
                    }
                });
            } catch (err) {
                console.error(err);
            }
        }

        setInterval(pollConversations, 3000);
    </script>
@endsection
