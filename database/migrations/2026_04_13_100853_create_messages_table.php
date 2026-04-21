<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')    // кто отправил
            ->constrained()
                ->cascadeOnDelete();
            $table->text('body')->nullable();          // текст (может быть пустым если только файл)
            $table->string('file_path')->nullable();   // путь к файлу в storage
            $table->string('file_name')->nullable();   // оригинальное имя файла
            $table->string('file_type')->nullable();   // 'image', 'video', 'document'
            $table->timestamp('read_at')->nullable();  // null = не прочитано
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
