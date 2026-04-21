@extends('layouts.app')

@section('title', 'Диалог с ' . $otherUser->name)

@section('content')
    <div class="max-w-[800px] w-full mx-auto px-4 flex flex-col h-[calc(100vh-57px)]">

        {{-- Шапка чата --}}
        <div class="bg-white border border-[#0000001a] rounded-t-[16px] px-5 py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#1588e2] flex items-center justify-center text-white font-semibold">
                {{ mb_strtoupper(mb_substr($otherUser->name, 0, 1)) }}
            </div>
            <div>
                <div class="font-semibold text-gray-900">{{ $otherUser->name }}</div>
                <div class="text-xs text-gray-400">{{ $otherUser->email }}</div>
            </div>
            <a href="{{ route('conversations.index') }}"
               class="ml-auto text-sm text-[#1588e2] hover:underline">
                ← Назад
            </a>
        </div>

        {{-- Область сообщений --}}
        <div id="messages-container"
             class="flex-1 overflow-y-auto bg-[#efefef] px-5 py-4 space-y-3 border-x border-[#0000001a]">
            @foreach($messages as $msg)
                @include('conversations._message', ['msg' => $msg])
            @endforeach
        </div>

        {{-- Форма отправки --}}
        <div class="bg-white border border-t-0 border-[#0000001a] rounded-b-[16px] px-5 py-4">

            {{-- Превью прикреплённого файла --}}
            <div id="file-preview" class="hidden mb-3 flex items-center gap-2 bg-[#efefef] rounded-lg px-3 py-2">
                <span id="file-preview-name" class="text-sm text-gray-700 flex-1 truncate"></span>
                <button onclick="clearFile()" class="text-gray-400 hover:text-red-500 text-xl leading-none">×</button>
            </div>

            <div class="flex items-end gap-3">

                {{-- Кнопка прикрепить файл --}}
                <label for="file-input" class="cursor-pointer text-gray-400 hover:text-[#1588e2] transition-colors pb-2 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </label>
                <input type="file" id="file-input" class="hidden"
                       accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip">

                {{-- Поле ввода --}}
                <textarea id="message-input"
                          placeholder="Напишите сообщение..."
                          rows="1"
                          class="flex-1 border border-[#ccc] rounded-[10px] px-4 py-2 text-base resize-none focus:outline-none focus:ring-2 focus:ring-[#1588e2] max-h-32"></textarea>

                {{-- Кнопка отправить --}}
                <button id="send-btn"
                        onclick="sendMessage()"
                        class="bg-[#1588e2] text-white rounded-[10px] px-5 py-2 text-base font-medium hover:opacity-80 active:scale-[0.98] flex-shrink-0">
                    Отправить
                </button>

            </div>
        </div>

    </div>

    <script>
        const CONVERSATION_ID = {{ $conversation->id }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        let lastMessageId = {{ $messages->last()?->id ?? 0 }};

        const container = document.getElementById('messages-container');

        // Скролл вниз
        function scrollToBottom() {
            container.scrollTop = container.scrollHeight;
        }
        scrollToBottom();

        // ========================================
        // ОТПРАВКА СООБЩЕНИЯ
        // ========================================
        async function sendMessage() {
            const input = document.getElementById('message-input');
            const fileInput = document.getElementById('file-input');
            const body = input.value.trim();
            const file = fileInput.files[0];

            if (!body && !file) return;

            const btn = document.getElementById('send-btn');
            btn.disabled = true;
            btn.textContent = '...';

            // FormData — позволяет отправить файл и текст одним запросом
            const formData = new FormData();
            if (body) formData.append('body', body);
            if (file) formData.append('file', file);

            try {
                const response = await fetch(`/conversations/${CONVERSATION_ID}/messages`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: formData,
                });

                if (!response.ok) throw new Error();

                const msg = await response.json();
                appendMessage(msg);
                lastMessageId = msg.id;

                input.value = '';
                clearFile();
                scrollToBottom();

            } catch {
                alert('Не удалось отправить сообщение.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Отправить';
            }
        }

        // ========================================
        // ДОБАВЛЕНИЕ СООБЩЕНИЯ В DOM
        // ========================================
        function appendMessage(msg) {
            const div = document.createElement('div');
            div.className = `flex ${msg.is_mine ? 'justify-end' : 'justify-start'}`;

            let content = '';

            if (msg.body) {
                content += `<p class="text-sm break-words">${escapeHtml(msg.body)}</p>`;
            }

            if (msg.file_url) {
                if (msg.file_type === 'image') {
                    content += `<a href="${msg.file_url}" target="_blank" class="block mt-1">
                                    <img src="${msg.file_url}" class="max-w-xs max-h-48 rounded-lg object-cover">
                                </a>`;
                } else if (msg.file_type === 'video') {
                    content += `<video controls class="max-w-xs rounded-lg mt-1">
                                    <source src="${msg.file_url}">
                                </video>`;
                } else {
                    content += `<a href="${msg.file_url}" target="_blank"= class="flex items-center gap-1 mt-1 text-sm underline opacity-80">
                             ${escapeHtml(msg.file_name)}
                                </a>`;
                }
            }

            div.innerHTML = `
        <div class="max-w-sm">
            ${!msg.is_mine ? `<p class="text-xs text-gray-500 mb-1 ml-1">${escapeHtml(msg.user_name)}</p>` : ''}
            <div class="rounded-2xl px-4 py-2 ${msg.is_mine
                ? 'bg-[#1588e2] text-white rounded-br-sm'
                : 'bg-white text-gray-900 rounded-bl-sm border border-[#0000001a]'}">
                ${content}
                <p class="text-xs mt-1 text-right ${msg.is_mine ? 'text-blue-200' : 'text-gray-400'}">
                    ${msg.created_at}
                </p>
            </div>
        </div>`;

            container.appendChild(div);
        }

        // ========================================
        // Проверяем новые сообщения каждые 3 сек
        // ========================================
        async function pollMessages() {
            try {
                const response = await fetch(`/conversations/${CONVERSATION_ID}/messages?after_id=${lastMessageId}`);
                const messages = await response.json();

                // если пришли новые сообщения
                if (messages.length > 0) {
                    const wasAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 50;

                    messages.forEach(msg => {
                        if (!msg.is_mine) {
                            appendMessage(msg); // добавляем каждое в DOM
                        }
                        lastMessageId = Math.max(lastMessageId, msg.id); // обновляем последний id
                    });

                    if (wasAtBottom) scrollToBottom();
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }

        // проверяем сообщения каждые 3 секунды
        setInterval(pollMessages, 3000);

        // ========================================
        // ФАЙЛ
        // ========================================
        document.getElementById('file-input').addEventListener('change', function () {
            if (this.files[0]) {
                document.getElementById('file-preview-name').textContent = ` ${this.files[0].name}`;
                document.getElementById('file-preview').classList.remove('hidden');
            }
        });

        function clearFile() {
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').classList.add('hidden');
        }

        document.getElementById('message-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // блокируем перенос строки
                sendMessage();
            }
        });

        // Защита от XSS — экранируем HTML в пользовательском тексте
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    </script>
@endsection
