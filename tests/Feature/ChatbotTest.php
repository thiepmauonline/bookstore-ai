<?php

namespace Tests\Feature;

use App\Livewire\Client\Chatbot;
use App\Models\Book;
use App\Models\ChatbotHistory;
use App\Models\Course;
use App\Models\Major;
use App\Services\Chatbot\BookRetriever;
use App\Services\Chatbot\ChatbotService;
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
        Http::assertSentCount(1);
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

    public function test_fallback_handles_small_talk_without_recommending_books(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        foreach (['bạn là ai' => 'Mình là trợ lý Bookstore AI', 'bạn chào lại tôi đi' => 'Chào bạn!', 'cảm ơn bạn' => 'Rất vui được giúp bạn!', 'giải thích thuật toán đệ quy' => 'tạm thời không kết nối được'] as $question => $expected) {
            Livewire::test(Chatbot::class)->set('question', $question)->call('send')->assertSee($expected);
            $history = ChatbotHistory::latest('id')->first();
            $this->assertSame([], $history->book_ids);
            $this->assertSame(ChatbotHistory::SOURCE_FALLBACK, $history->source);
        }
        Http::assertNothingSent();
    }

    public function test_transient_ai_failure_is_retried_once_and_recovers(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::sequence()
            ->push(['error' => ['message' => 'overloaded']], 503)
            ->push(['candidates' => [['content' => ['parts' => [['text' => 'Chào bạn!']]]]]])]);

        Livewire::test(Chatbot::class)->set('question', 'xin chào')->call('send')->assertSee('Chào bạn!');
        Http::assertSentCount(2);
        $this->assertSame(ChatbotHistory::SOURCE_AI, ChatbotHistory::sole()->source);
    }

    public function test_repeated_transient_failure_stops_after_two_attempts(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'overloaded']], 503)]);

        Livewire::test(Chatbot::class)->set('question', 'bạn là ai')->call('send')->assertSee('Mình là trợ lý Bookstore AI');
        Http::assertSentCount(2);
        $this->assertSame(ChatbotHistory::SOURCE_FALLBACK, ChatbotHistory::sole()->source);
    }

    public function test_empty_question_is_rejected(): void
    {
        Livewire::test(Chatbot::class)->set('question', '')->call('send')->assertHasErrors('question');
        $this->assertSame(0, ChatbotHistory::count());
    }

    public function test_cheaper_follow_up_searches_same_course_instead_of_best_sellers(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        $chatbot->ask('Lập trình Web', 'follow-up');
        $cheap = Book::create([
            'title' => 'Thực Hành Web', 'course_id' => $this->webBook->course_id,
            'price' => 90000, 'quantity' => 5,
        ]);
        Book::create(['title' => 'Kinh Tế Đại Cương', 'course_id' => $this->economicsBook->course_id, 'price' => 20000, 'quantity' => 5]);

        $answer = $chatbot->ask('Có cuốn nào rẻ hơn không?', 'follow-up');
        $this->assertSame([$cheap->id], $answer->book_ids);
        $this->assertStringContainsString('90.000đ', $answer->answer);
    }

    public function test_budget_follow_up_filters_prices_and_does_not_leak_between_sessions(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        $chatbot->ask('Lập trình Web', 'web-session');
        $answer = $chatbot->ask('Ngân sách của mình là 200k', 'web-session');
        $this->assertSame([$this->webBook->id], $answer->book_ids);

        $otherSession = $chatbot->ask('Ngân sách của mình là 200k', 'new-session');
        $this->assertSame([], $otherSession->book_ids);
        $this->assertStringContainsString('ngành hoặc học phần', $otherSession->answer);
    }

    public function test_cheaper_follow_up_can_repeat_the_course_name(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        $chatbot->ask('Lập trình Web', 'explicit-course');
        $cheap = Book::create(['title' => 'Thực Hành Web', 'course_id' => $this->webBook->course_id, 'price' => 90000, 'quantity' => 5]);

        $answer = $chatbot->ask('Có sách lập trình Web nào rẻ hơn không?', 'explicit-course');
        $this->assertSame([$cheap->id], $answer->book_ids);
    }

    public function test_unavailable_or_over_budget_books_do_not_become_unrelated_recommendations(): void
    {
        config(['services.gemini.key' => null]);
        $this->webBook->update(['quantity' => 0]);
        $answer = app(ChatbotService::class)->ask('Sách lập trình web dưới 200k', 'no-stock');
        $this->assertSame([], $answer->book_ids);
        $this->assertStringContainsString('chưa tìm được sách phù hợp', $answer->answer);

        $answer = app(ChatbotService::class)->ask('Sách kinh tế vĩ mô dưới 100k', 'no-budget');
        $this->assertSame([], $answer->book_ids);
    }

    public function test_new_topic_replaces_previous_course_for_budget_search(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        $chatbot->ask('Lập trình Web', 'topic-change');
        $answer = $chatbot->ask('Giờ tìm sách kinh tế vĩ mô dưới 300k', 'topic-change');
        $this->assertSame([$this->economicsBook->id], $answer->book_ids);
    }

    public function test_budget_accepts_thousand_separators_and_decimal_millions(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        foreach (['Sách Web dưới 200.000đ', 'Sách Web tối đa 200 nghìn', 'Sách Web dưới 0,2 triệu', 'Sách Web dưới 200000 VND'] as $question) {
            $this->assertSame([$this->webBook->id], $chatbot->ask($question, 'money-'.md5($question))->book_ids, $question);
        }
    }

    public function test_vietnamese_accents_still_distinguish_micro_and_macro_economics(): void
    {
        config(['services.gemini.key' => null]);
        $microCourse = Course::create(['major_id' => $this->economicsBook->course->major_id, 'name' => 'Kinh tế vi mô']);
        $micro = Book::create(['title' => 'Kinh Tế Vi Mô', 'course_id' => $microCourse->id, 'price' => 100000, 'quantity' => 5]);
        $answer = app(ChatbotService::class)->ask('Kinh tế vĩ mô', 'accents');
        $this->assertSame($this->economicsBook->id, $answer->book_ids[0]);
        $answer = app(ChatbotService::class)->ask('Kinh tế vi mô', 'accents');
        $this->assertSame($micro->id, $answer->book_ids[0]);
    }

    public function test_ai_receives_only_books_matching_follow_up_budget(): void
    {
        config(['services.gemini.key' => null]);
        $chatbot = app(ChatbotService::class);
        $chatbot->ask('Lập trình Web', 'ai-follow-up');
        $cheap = Book::create(['title' => 'Thực Hành Web', 'course_id' => $this->webBook->course_id, 'price' => 90000, 'quantity' => 5]);
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => "Bạn tham khảo Thực Hành Web [#{$cheap->id}]."]]]]]])]);

        $answer = $chatbot->ask('Có cuốn nào rẻ hơn không?', 'ai-follow-up');
        $this->assertSame([$cheap->id], $answer->book_ids);
        Http::assertSent(fn ($request) => str_contains($request['system_instruction']['parts'][0]['text'], "[#{$cheap->id}]")
            && ! str_contains($request['system_instruction']['parts'][0]['text'], "[#{$this->webBook->id}] {$this->webBook->title}")
            && count($request['contents']) === 3);
    }
}
