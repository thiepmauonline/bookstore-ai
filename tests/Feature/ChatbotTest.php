<?php

namespace Tests\Feature;

use App\Livewire\Client\Chatbot;
use App\Models\Book;
use App\Models\ChatbotHistory;
use App\Models\Course;
use App\Models\Major;
use App\Services\Chatbot\BookRetriever;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    private Book $webBook;

    private Book $economicsBook;

    protected function setUp(): void
    {
        parent::setUp();

        $it = Major::create(['name' => 'Công nghệ thông tin']);
        $eco = Major::create(['name' => 'Quản trị kinh doanh']);
        $web = Course::create(['major_id' => $it->id, 'name' => 'Lập trình Web']);
        $macro = Course::create(['major_id' => $eco->id, 'name' => 'Kinh tế vĩ mô']);

        $this->webBook = Book::create(['title' => 'Lập Trình Web Căn Bản', 'course_id' => $web->id, 'price' => 150000, 'quantity' => 5]);
        $this->economicsBook = Book::create(['title' => 'Kinh Tế Học Vĩ Mô', 'course_id' => $macro->id, 'price' => 250000, 'quantity' => 5]);
    }

    public function test_retriever_matches_course_without_vietnamese_accents(): void
    {
        $result = app(BookRetriever::class)->search('sach cho mon lap trinh web');

        $this->assertTrue($result['matched']);
        $this->assertSame($this->webBook->id, $result['books']->first()->id);
        $this->assertNotContains($this->economicsBook->id, $result['books']->pluck('id'));
    }

    public function test_falls_back_to_catalog_search_when_ai_is_not_configured(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        Livewire::test(Chatbot::class)
            ->set('question', 'Giáo trình kinh tế vĩ mô')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Kinh Tế Học Vĩ Mô');

        Http::assertNothingSent();
        $history = ChatbotHistory::sole();
        $this->assertSame(ChatbotHistory::SOURCE_FALLBACK, $history->source);
        $this->assertSame([$this->economicsBook->id], $history->book_ids);
    }

    public function test_ai_answer_keeps_only_books_from_context_and_strips_markers(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [[
                    'text' => "Bạn nên đọc **Lập Trình Web Căn Bản** [#{$this->webBook->id}]. Ngoài ra có sách [#9999].",
                ]]]]],
            ]),
        ]);

        Livewire::test(Chatbot::class)
            ->set('question', 'Sách môn lập trình web?')
            ->call('send')
            ->assertHasNoErrors();

        $history = ChatbotHistory::sole();
        $this->assertSame(ChatbotHistory::SOURCE_AI, $history->source);
        $this->assertSame([$this->webBook->id], $history->book_ids, 'Mã sách không có trong ngữ cảnh (#9999) phải bị loại.');
        $this->assertStringNotContainsString('[#', $history->answer);

        Http::assertSent(function ($request) {
            $system = $request['system_instruction']['parts'][0]['text'];

            return $request->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($system, "[#{$this->webBook->id}] Lập Trình Web Căn Bản")
                && $request['contents'][0]['parts'][0]['text'] === 'Sách môn lập trình web?';
        });
    }

    public function test_api_error_falls_back_instead_of_failing(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'quota exceeded']], 429)]);

        Livewire::test(Chatbot::class)
            ->set('question', 'lập trình web')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Lập Trình Web Căn Bản');

        $this->assertSame(ChatbotHistory::SOURCE_FALLBACK, ChatbotHistory::sole()->source);
    }

    public function test_feedback_is_recorded_once_and_only_for_own_session(): void
    {
        config(['services.gemini.key' => null]);

        $component = Livewire::test(Chatbot::class)->set('question', 'lập trình web')->call('send');
        $history = ChatbotHistory::sole();

        $component->call('feedback', $history->id, false)->call('feedback', $history->id, true);
        $this->assertTrue($history->feedback()->sole()->is_helpful);

        $foreign = ChatbotHistory::create(['session_id' => 'someone-else', 'question' => 'q', 'answer' => 'a']);
        $this->expectException(ModelNotFoundException::class);
        $component->call('feedback', $foreign->id, true);
    }

    public function test_empty_question_is_rejected(): void
    {
        Livewire::test(Chatbot::class)->set('question', '')->call('send')->assertHasErrors('question');
        $this->assertSame(0, ChatbotHistory::count());
    }
}
