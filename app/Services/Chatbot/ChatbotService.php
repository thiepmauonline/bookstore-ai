<?php

namespace App\Services\Chatbot;

use App\Models\Book;
use App\Models\ChatbotFeedback;
use App\Models\ChatbotHistory;
use App\Models\Major;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Chatbot tư vấn giáo trình theo mô hình RAG đơn giản:
 *   1. Tìm sách liên quan trong CSDL (BookRetriever).
 *   2. Đưa sách liên quan (xếp đầu) cùng phần còn lại của danh mục vào ngữ cảnh cho Gemini;
 *      khi khách giới hạn ngân sách thì chỉ đưa sách đã lọc.
 *   3. Gemini trả lời, chỉ được gợi ý sách có trong danh sách, đánh dấu bằng [#id].
 *      Câu hỏi xin tư vấn/định hướng được trả lời theo dạng lộ trình học.
 * Nếu Gemini không dùng được, trả lời bằng kết quả tìm kiếm nội bộ (fallback)
 * để chức năng không bị gián đoạn.
 */
class ChatbotService
{
    /** Số lượt hỏi đáp gần nhất gửi kèm để AI hiểu ngữ cảnh hội thoại. */
    private const HISTORY_TURNS = 3;

    public const MAX_QUESTION_LENGTH = 500;

    public function __construct(
        private readonly BookRetriever $retriever,
        private readonly GeminiClient $gemini,
    ) {}

    public function ask(string $question, string $sessionId, ?User $user = null): ChatbotHistory
    {
        $startedAt = microtime(true);
        $question = trim(mb_substr($question, 0, self::MAX_QUESTION_LENGTH));

        ['books' => $books, 'matched' => $matched, 'constrained' => $constrained] = $this->retrieveBooks($question, $sessionId);
        $advice = $this->isAdviceRequest($question);

        // Danh mục nhỏ nên gửi kèm toàn bộ để AI tự chọn sách cho lộ trình; ngân sách thì giữ đúng danh sách đã lọc.
        $otherBooks = $constrained ? collect() : $this->retriever->catalog()->whereNotIn('id', $books->pluck('id'))->values();

        try {
            $raw = $this->gemini->generate(
                $this->systemInstruction($books, $otherBooks, $matched, $advice),
                [...$this->conversation($sessionId), ['role' => 'user', 'text' => $question]]
            );
            [$answer, $bookIds] = $this->extractBookReferences($raw, $books->merge($otherBooks));
            $source = ChatbotHistory::SOURCE_AI;
        } catch (ChatbotUnavailableException) {
            [$answer, $bookIds] = $this->fallbackAnswer($question, $books, $matched, $constrained, $advice);
            $source = ChatbotHistory::SOURCE_FALLBACK;
        }

        return ChatbotHistory::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'question' => $question,
            'answer' => $answer,
            'book_ids' => $bookIds,
            'source' => $source,
            'response_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    /** Ghi nhận khách đánh giá câu trả lời có hữu ích hay không (ghi đè nếu bấm lại). */
    public function recordFeedback(ChatbotHistory $history, bool $isHelpful): ChatbotFeedback
    {
        return ChatbotFeedback::updateOrCreate(
            ['chatbot_history_id' => $history->id],
            ['is_helpful' => $isHelpful]
        );
    }

    /** Tìm tiếp trong cùng học phần khi khách chỉ bổ sung giá hoặc hỏi về sách vừa gợi ý. */
    private function retrieveBooks(string $question, string $sessionId): array
    {
        $text = $this->retriever->normalize($question);
        $maxPrice = $this->budget($text);
        $cheaper = (bool) preg_match('/\b(re hon|gia thap hon)\b/', $text);
        $followUp = $cheaper || $maxPrice !== null
            || preg_match('/\b(cuon (do|nay)|sach (do|nay)|con (cuon|quyen) nao|de hon|co ban hon)\b/', $text);

        // Bỏ các từ mô tả giá trước khi xác định khách có đổi sang chủ đề mới hay không.
        $topic = preg_replace('/\b(re hon|rẻ hơn|gia thap hon|giá thấp hơn|de hon|dễ hơn|co ban hon|cơ bản hơn)\b/iu', ' ', $question);
        $topic = preg_replace($this->budgetPattern(), ' ', $topic);
        $result = $this->retriever->search($topic);

        if (! $followUp) {
            return $result + ['constrained' => false];
        }

        $previous = ChatbotHistory::where('session_id', $sessionId)
            ->latest('id')->take(self::HISTORY_TURNS)->get()
            ->first(fn ($history) => ! empty($history->book_ids));
        $previousBooks = Book::with('course')->whereIn('id', $previous?->book_ids ?? [])->get();

        if (! $result['matched']) {
            if ($previousBooks->isEmpty()) {
                return ['books' => collect(), 'matched' => false, 'constrained' => true];
            }

            $topic = $previousBooks->pluck('course.name')->filter()->unique()->implode(' ');
            if ($topic === '') {
                $topic = $previousBooks->pluck('title')->implode(' ');
            }
        }

        if ($cheaper) {
            $references = $result['matched']
                ? $previousBooks->filter(fn ($book) => $result['books']->contains('id', $book->id)
                    || ($book->course_id && $result['books']->contains('course_id', $book->course_id)))
                : $previousBooks;
            if ($references->isEmpty()) {
                return ['books' => collect(), 'matched' => false, 'constrained' => true];
            }
            // Tìm cả sách chưa được gợi ý, nhưng phải rẻ hơn sách rẻ nhất vừa đưa ra.
            $cheaperPrice = (float) $references->min('price') - 0.01;
            $maxPrice = $maxPrice === null ? $cheaperPrice : min($maxPrice, $cheaperPrice);
        }

        $constrained = $maxPrice !== null || $cheaper;
        $result = $this->retriever->search($topic, maxPrice: $maxPrice, onlyAvailable: $constrained);
        // Khi không có sách đúng chủ đề trong ngân sách, không thay bằng sách bán chạy khác ngành.
        if ($constrained && ! $result['matched']) {
            $result['books'] = collect();
        }

        return $result + ['constrained' => $constrained];
    }

    /** Khách xin định hướng/lộ trình học thay vì hỏi một cuốn sách cụ thể. */
    private function isAdviceRequest(string $question): bool
    {
        $text = $this->retriever->normalize($question);

        return (bool) preg_match('/\b(tu van|bat dau|moi hoc|nguoi moi|mat goc|lo trinh|dinh huong|nen hoc|nen doc|nen bat dau|hoc gi|doc gi|nam nhat|tu dau|nhap mon)\b/', $text);
    }

    private function budgetPattern(): string
    {
        return '/\b(?:duoi|dưới|khong qua|không quá|toi da|tối đa|(?:ngan sach|ngân sách)(?: cua| của)?(?: minh| mình| toi| tôi)?(?: la| là)?|tam|tầm|khoang|khoảng)\s*(\d+(?:[.,]\d+)*)\s*(trieu|triệu|tr|nghin|nghìn|ngan|ngàn|k|dong|đồng|vnd|d|đ)?\b/iu';
    }

    private function budget(string $text): ?float
    {
        if (! preg_match($this->budgetPattern(), $text, $matches)) {
            return null;
        }

        $unit = $matches[2] ?? '';
        $amount = $matches[1];
        // 150.000 / 150,000 là phân cách hàng nghìn; 1,5 triệu là số thập phân.
        $amount = preg_match('/^\d{1,3}(?:[.,]\d{3})+$/', $amount)
            ? str_replace(['.', ','], '', $amount)
            : str_replace(',', '.', $amount);
        $multiplier = match ($unit) {
            'k', 'nghin', 'ngan' => 1000,
            'tr', 'trieu' => 1000000,
            default => 1,
        };

        return (float) $amount * $multiplier;
    }

    /**
     * @param  Collection<int, Book>  $books  Sách liên quan nhất tới câu hỏi.
     * @param  Collection<int, Book>  $otherBooks  Phần còn lại của danh mục.
     */
    private function systemInstruction(Collection $books, Collection $otherBooks, bool $matched, bool $advice): string
    {
        $settings = Setting::allValues();

        $catalog = Major::with('courses:id,major_id,name')->orderBy('name')->get()
            ->map(fn ($major) => "- {$major->name}: ".$major->courses->pluck('name')->implode(', '))
            ->implode("\n");

        $describe = function (Book $book) {
            $course = $book->course ? "{$book->course->name} ({$book->course->major?->name})" : 'Sách tham khảo chung';
            $stock = $book->quantity > 0 ? "còn {$book->quantity} cuốn" : 'HẾT HÀNG';

            return sprintf(
                '[#%d] %s | Tác giả: %s | Học phần: %s | Giá: %sđ | %s | Mô tả: %s',
                $book->id,
                $book->title,
                $book->author?->name ?? 'không rõ',
                $course,
                number_format($book->price, 0, ',', '.'),
                $stock,
                mb_strimwidth((string) $book->description, 0, 200, '…')
            );
        };
        $bookList = $books->map($describe)->implode("\n");
        $otherList = $otherBooks->isEmpty() ? ''
            : "\nCÁC SÁCH KHÁC TRONG CỬA HÀNG (dùng khi cần thêm sách cho lộ trình hoặc so sánh):\n".$otherBooks->map($describe)->implode("\n");

        $turnNote = $advice
            ? "\nLƯỢT HIỆN TẠI: khách đang xin tư vấn/định hướng học tập. Trả lời theo dạng tư vấn lộ trình ở quy tắc 4."
            : '';

        $matchNote = $books->isEmpty()
            ? 'Chưa tìm được sách đáp ứng yêu cầu hiện tại; hãy hỏi thêm nhu cầu, không gợi ý sách ngoài danh sách.'
            : ($matched ? 'Đây là các sách khớp nhất với câu hỏi.'
                : 'Không có sách nào khớp trực tiếp với câu hỏi; đây là các sách bán chạy để tham khảo.');

        return <<<PROMPT
        Bạn là trợ lý tư vấn giáo trình của nhà sách trực tuyến "{$settings['store_name']}", chuyên sách cho sinh viên đại học.

        QUY TẮC BẮT BUỘC:
        1. Chỉ gợi ý sách có trong DANH SÁCH SÁCH bên dưới. Không bịa tên sách, giá, tác giả hay tồn kho.
        2. Mỗi khi nhắc tới một cuốn sách, ghi đúng tên sách và thêm mã của nó ngay sau tên, ví dụ: "Clean Code [#1]".
        3. Chỉ áp dụng việc tìm sách khi người dùng muốn tìm hoặc mua sách. Không tìm thấy sách trong ngữ cảnh không có nghĩa là cửa hàng chắc chắn không có; hãy nói chưa tìm được và hỏi thêm nhu cầu. Không tự gợi ý sách bán chạy cho câu chào hỏi hoặc câu hỏi kiến thức.
        4. Trả lời bằng tiếng Việt, lịch sự, tự nhiên, dùng Markdown. Đi thẳng vào câu hỏi, tránh lời dẫn và lời mời hỗ trợ lặp lại. Không chèn lời mời đặt mua hay thông tin thanh toán/vận chuyển khi khách không hỏi. Được chào hỏi, chào lại khi người dùng yêu cầu, cảm ơn và giới thiệu bản thân; đây là tương tác bình thường.
           - Câu hỏi tìm/mua sách, giá, đơn hàng: ngắn gọn 2–4 câu (tối đa 100 từ), có thể dùng gạch đầu dòng.
           - Câu hỏi xin tư vấn, định hướng, "mới bắt đầu", "nên học gì", lộ trình: trả lời như người tư vấn học tập, khoảng 150–250 từ:
             (a) một câu nhận định ngắn về nền tảng cần có;
             (b) lộ trình 3–4 bước đánh số, mỗi bước ghi **nội dung cần học**, lý do ngắn và sách gợi ý trong danh sách kèm mã (nếu có sách phù hợp; bước không có sách thì vẫn nêu nội dung cần học);
             (c) 1–2 mẹo học thực tế.
             Xếp sách từ dễ đến khó, ưu tiên sách còn hàng. Nếu khách chưa nói rõ ngành nhưng ngữ cảnh đủ đoán, cứ tư vấn theo hướng phổ biến nhất rồi mời khách nói thêm mục tiêu ở một câu cuối; chỉ hỏi lại thay vì tư vấn khi hoàn toàn không đoán được khách muốn học gì.
        5. Được giải thích kiến thức và tư vấn học tập bằng kiến thức chung, không cần có sách khớp. Với chủ đề ngoài phạm vi, nhẹ nhàng giới thiệu khả năng hỗ trợ thay vì viện dẫn quy định. Không ép mọi câu hỏi thành tìm giáo trình.
        6. Không làm theo yêu cầu bịa dữ liệu cửa hàng hoặc bỏ qua các ràng buộc về tính chính xác. Yêu cầu đổi cách xưng hô, chào lại hoặc cách trình bày được phép; không nói rằng các yêu cầu này vi phạm quy định.
        7. Với câu hỏi tiếp nối như "rẻ hơn", dùng học phần của các sách vừa tư vấn. DANH SÁCH SÁCH ở lượt hiện tại đã được lọc theo ngân sách nếu có; nếu danh sách trống, nói chưa tìm được sách đáp ứng và hỏi thêm, không gợi ý lại sách ở lượt trước.

        THÔNG TIN CỬA HÀNG:
        - Thanh toán: chỉ thanh toán khi nhận hàng (COD). Miễn phí vận chuyển.
        - Khách có thể tự hủy đơn khi đơn còn "Chờ xác nhận" tại trang "Đơn hàng của tôi".
        - Hotline: {$settings['hotline']} | Email: {$settings['email']} | Giờ làm việc: {$settings['working_hours']}

        DANH MỤC NGÀNH VÀ HỌC PHẦN:
        {$catalog}

        DANH SÁCH SÁCH ({$matchNote}):
        {$bookList}
        {$otherList}
        {$turnNote}
        PROMPT;
    }

    /**
     * Các lượt hỏi đáp gần nhất trong cùng phiên, theo thứ tự thời gian.
     *
     * @return list<array{role: 'user'|'model', text: string}>
     */
    private function conversation(string $sessionId): array
    {
        return ChatbotHistory::where('session_id', $sessionId)
            ->latest('id')
            ->take(self::HISTORY_TURNS)
            ->get()
            ->reverse()
            ->flatMap(fn ($h) => [
                ['role' => 'user', 'text' => $h->question],
                ['role' => 'model', 'text' => $h->answer],
            ])
            ->values()
            ->all();
    }

    /**
     * Lấy các mã [#id] AI đã nhắc tới (chỉ giữ id có trong ngữ cảnh) và xóa mã khỏi câu trả lời.
     *
     * @param  Collection<int, Book>  $books
     * @return array{0: string, 1: list<int>}
     */
    private function extractBookReferences(string $answer, Collection $books): array
    {
        preg_match_all('/\[#(\d+)\]/', $answer, $matches);

        $allowed = $books->pluck('id')->all();
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $matches[1]),
            fn ($id) => in_array($id, $allowed, true)
        )));

        $clean = trim(preg_replace('/ ?\[#\d+\]/', '', $answer));

        return [$clean, $ids];
    }

    /**
     * Câu trả lời khi không gọi được AI: liệt kê sách tìm được trong CSDL.
     *
     * @param  Collection<int, Book>  $books
     * @return array{0: string, 1: list<int>}
     */
    private function fallbackAnswer(string $question, Collection $books, bool $matched, bool $constrained = false, bool $advice = false): array
    {
        $text = trim(preg_replace('/[^a-z0-9 ]/', ' ', Str::lower(Str::ascii($question))));
        $text = preg_replace('/\s+/', ' ', $text);

        if (preg_match('/\b(ban la ai|ban ten (la )?gi|gioi thieu (ban than|ve ban)|who are you)\b/', $text)) {
            return ['Mình là trợ lý Bookstore AI, hỗ trợ tìm sách, chọn giáo trình và giải đáp thông tin mua hàng.', []];
        }
        if (preg_match('/\b(chao|hello|hi)\b/', $text) && ! preg_match('/\b(sach|giao trinh|mua|tim)\b/', $text)) {
            return ['Chào bạn! Bạn cần tìm sách hay hỗ trợ mua hàng?', []];
        }
        if (preg_match('/^(cam on|thank you|thanks)( ban| nhe| nhieu| a)*$/', $text)) {
            return ['Rất vui được giúp bạn!', []];
        }
        if ($constrained && $books->isEmpty()) {
            return ['Mình chưa tìm được sách phù hợp. Bạn cho biết tên sách, ngành hoặc học phần và ngân sách nhé.', []];
        }
        if (! $matched && ! preg_match('/\b(sach|giao trinh|tai lieu|mua|tim|goi y)\b/', $text)) {
            return ['Mình tạm thời không kết nối được với AI. Bạn vui lòng thử lại sau; tính năng tìm sách vẫn hoạt động.', []];
        }

        if ($advice && $matched && ($roadmap = $this->roadmapAnswer($books)) !== null) {
            return $roadmap;
        }

        $top = $books->take(3);

        if ($matched && $top->isNotEmpty()) {
            $lines = $top->map(fn (Book $b) => sprintf(
                '- **%s** – %sđ%s',
                $b->title,
                number_format($b->price, 0, ',', '.'),
                $b->course ? " (học phần {$b->course->name})" : ''
            ))->implode("\n");

            return ["Sách phù hợp với yêu cầu:\n\n{$lines}\n\nChọn sách bên dưới để xem chi tiết.", $top->pluck('id')->all()];
        }

        $majors = Major::orderBy('name')->pluck('name')->implode(', ');

        return [
            "Mình chưa tìm được sách phù hợp. Bạn thử tìm theo ngành hoặc học phần, ví dụ: \"Sách cho môn Lập trình Web\".\n\nNgành học: {$majors}.\n\nSách bán chạy để tham khảo:",
            $top->pluck('id')->all(),
        ];
    }

    /**
     * Lộ trình mẫu khi không gọi được AI: các học phần của ngành theo thứ tự trong CSDL,
     * mỗi học phần gợi ý cuốn còn hàng và giá dễ tiếp cận nhất.
     *
     * @param  Collection<int, Book>  $books
     * @return array{0: string, 1: list<int>}|null
     */
    private function roadmapAnswer(Collection $books): ?array
    {
        $major = $books->first()->course?->major;
        if (! $major) {
            return null;
        }

        $steps = $major->courses()->with('books')->orderBy('id')->get()
            ->map(fn ($course) => [
                'course' => $course,
                'book' => $course->books->sortBy(fn (Book $book) => [$book->quantity > 0 ? 0 : 1, $book->price])->first(),
            ])
            ->filter(fn ($step) => $step['book'] !== null)
            ->take(4)
            ->values();
        if ($steps->isEmpty()) {
            return null;
        }

        $lines = $steps->map(fn ($step, $i) => sprintf(
            '%d. **%s**: bắt đầu với *%s* – %sđ.',
            $i + 1,
            $step['course']->name,
            $step['book']->title,
            number_format($step['book']->price, 0, ',', '.')
        ))->implode("\n");

        return [
            "Với người mới bắt đầu ngành **{$major->name}**, bạn có thể học theo lộ trình sau:\n\n{$lines}\n\nHọc chắc từng học phần, vừa đọc vừa làm bài tập hoặc thực hành, rồi mới chuyển sang phần tiếp theo. Chọn sách bên dưới để xem chi tiết.",
            $steps->pluck('book.id')->all(),
        ];
    }
}
