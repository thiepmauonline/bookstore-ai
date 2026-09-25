<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chatbot:check {question=Sách cho môn Lập trình Web}', function (string $question) {
    $gemini = app(\App\Services\Chatbot\GeminiClient::class);

    $this->line('Model: '.config('services.gemini.model'));
    if (! $gemini->isConfigured()) {
        $this->error('Chưa có GEMINI_API_KEY trong .env. Chatbot đang chạy chế độ dự phòng (tìm kiếm nội bộ).');

        return 1;
    }

    try {
        $answer = $gemini->generate('Trả lời ngắn gọn bằng tiếng Việt.', [['role' => 'user', 'text' => $question]]);
    } catch (\App\Services\Chatbot\ChatbotUnavailableException $e) {
        $this->error('Gọi Gemini thất bại: '.$e->getMessage().' Xem chi tiết trong storage/logs/laravel.log.');

        return 1;
    }

    $this->info('Kết nối Gemini thành công. Câu trả lời thử:');
    $this->line($answer);

    return 0;
})->purpose('Kiểm tra kết nối tới Google Gemini cho chatbot');
