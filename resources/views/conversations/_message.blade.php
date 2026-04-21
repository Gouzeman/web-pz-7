@php $isMine = $msg->user_id === Auth::id(); @endphp

<div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-sm">

        @unless($isMine)
            <p class="text-xs text-gray-500 mb-1 ml-1">{{ $msg->user->name }}</p>
        @endunless

        <div class="rounded-2xl px-4 py-2 {{ $isMine
            ? 'bg-[#1588e2] text-white rounded-br-sm'
            : 'bg-white text-gray-900 rounded-bl-sm border border-[#0000001a]' }}">

            @if($msg->body)
                <p class="text-sm break-words">{{ $msg->body }}</p>
            @endif

            @if($msg->file_path)
                @php $fileUrl = Storage::disk('public')->url($msg->file_path); @endphp

                @if($msg->file_type === 'image')
                    <a href="{{ $fileUrl }}" target="_blank" class="block mt-1">
                        <img src="{{ $fileUrl }}" class="max-w-xs max-h-48 rounded-lg object-cover">
                    </a>
                @elseif($msg->file_type === 'video')
                    <video controls class="max-w-xs rounded-lg mt-1">
                        <source src="{{ $fileUrl }}">
                    </video>
                @else
                    <a href="{{ $fileUrl }}" target="_blank"
                       class="flex items-center gap-1 mt-1 text-sm underline {{ $isMine ? 'text-blue-200' : 'text-blue-600' }}">
                        📎 {{ $msg->file_name ?? 'Скачать файл' }}
                    </a>
                @endif
            @endif

            <p class="text-xs mt-1 text-right {{ $isMine ? 'text-blue-200' : 'text-gray-400' }}">
                {{ $msg->created_at->format('H:i') }}
            </p>

        </div>
    </div>
</div>
