<?php

namespace Tests\Feature;

use App\Models\ChatbotHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_thirty_question_evaluation_generates_report_without_saving_demo_conversations(): void
    {
        $this->seed();
        $before = ChatbotHistory::count();
        $path = storage_path('framework/testing/chatbot-evaluation.md');
        config(['services.gemini.key' => 'test-key']);
        Http::fake();

        try {
            $exitCode = Artisan::call('chatbot:evaluate', ['--output' => $path]);
            $this->assertSame(0, $exitCode, Artisan::output());
            $report = file_get_contents($path);
            $this->assertStringContainsString('đạt: 30/30', $report);
            $this->assertStringContainsString('Nguồn AI: 0; dự phòng: 30', $report);
            $this->assertSame($before, ChatbotHistory::count());
            $this->assertSame('test-key', config('services.gemini.key'));
            Http::assertNothingSent();
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function test_ai_evaluation_is_not_successful_when_all_requests_use_fallback(): void
    {
        $this->seed();
        $before = ChatbotHistory::count();
        $path = storage_path('framework/testing/chatbot-evaluation-ai.md');
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'Invalid API key']], 401)]);

        try {
            $this->assertSame(1, Artisan::call('chatbot:evaluate', ['--ai' => true, '--output' => $path]));
            $this->assertStringContainsString('Nguồn AI: 0; dự phòng: 30', file_get_contents($path));
            $this->assertSame($before, ChatbotHistory::count());
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
