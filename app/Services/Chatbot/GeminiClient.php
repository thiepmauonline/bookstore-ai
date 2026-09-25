<?php

namespace App\Services\Chatbot;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gọi Google Gemini API (REST generateContent).
 * Tài liệu: https://ai.google.dev/api/generate-content
 */
class GeminiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * @param  list<array{role: 'user'|'model', text: string}>  $messages  Hội thoại, tin cuối là câu hỏi hiện tại.
     *
     * @throws ChatbotUnavailableException
     */
    public function generate(string $systemInstruction, array $messages): string
    {
        if (! $this->isConfigured()) {
            throw new ChatbotUnavailableException('Chưa cấu hình GEMINI_API_KEY.');
        }

        $url = sprintf('%s/models/%s:generateContent', rtrim(config('services.gemini.base_url'), '/'), config('services.gemini.model'));

        try {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(config('services.gemini.timeout'))
                ->acceptJson()
                ->post($url, [
                    'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => array_map(fn ($m) => [
                        'role' => $m['role'],
                        'parts' => [['text' => $m['text']]],
                    ], $messages),
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 2048,
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Gemini connection failed', ['error' => $e->getMessage()]);
            throw new ChatbotUnavailableException('Không kết nối được tới Gemini.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Gemini request failed', [
                'status' => $response->status(),
                'error' => $response->json('error.message'),
            ]);
            throw new ChatbotUnavailableException('Gemini trả về lỗi HTTP '.$response->status().'.');
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode('');

        if (trim($text) === '') {
            Log::warning('Gemini returned empty answer', ['finishReason' => $response->json('candidates.0.finishReason')]);
            throw new ChatbotUnavailableException('Gemini không trả về nội dung.');
        }

        return trim($text);
    }
}
