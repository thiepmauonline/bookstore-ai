<?php

namespace App\Services\Chatbot;

use App\Enums\OrderStatus;
use App\Models\Book;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tìm các cuốn sách liên quan tới câu hỏi để làm ngữ cảnh cho chatbot.
 *
 * So khớp theo từ khóa, không phân biệt dấu tiếng Việt, có trọng số theo trường:
 * học phần và tên sách quan trọng hơn mô tả. Cách làm này đủ tốt với danh mục
 * vài trăm đến vài nghìn đầu sách mà không cần thêm hạ tầng tìm kiếm riêng.
 */
class BookRetriever
{
    /** Trọng số điểm khi từ khóa xuất hiện trong từng trường. */
    private const WEIGHTS = [
        'course' => 5,
        'title' => 4,
        'major' => 3,
        'category' => 2,
        'author' => 2,
        'description' => 1,
    ];

    /** Kết quả phải đạt ít nhất tỉ lệ này so với điểm cao nhất mới được giữ lại. */
    private const MIN_RELATIVE_SCORE = 0.4;

    /** Cụm từ chung chung, bỏ đi trước khi tách từ (đã bỏ dấu). */
    private const STOP_PHRASES = [
        'giao trinh', 'tai lieu', 'mon hoc', 'hoc phan', 'nganh hoc', 'goi y', 'tu van', 'cuon sach', 'quyen sach',
    ];

    /** Từ phổ biến, không mang nghĩa tìm kiếm (đã bỏ dấu). */
    private const STOPWORDS = [
        'toi', 'minh', 'ban', 'em', 'anh', 'chi', 'can', 'muon', 'tim', 'mua', 'hoi', 'xin', 'giup',
        'sach', 'cuon', 'quyen', 'hoc', 'nganh', 'mon', 'cho', 've', 'gi', 'nao', 'co', 'khong', 'la',
        'cua', 'va', 'voi', 'nhe', 'nhung', 'cac', 'mot', 'hay', 'nen', 'duoc', 'thi', 'dang', 'nay',
        'do', 'kia', 'shop', 'oi', 'ah', 'vay', 'nhu', 'nhat', 'loai', 'dau',
    ];

    /**
     * @return array{books: Collection<int, Book>, matched: bool}
     *                                                            matched = false nghĩa là không khớp từ khóa nào, trả về sách bán chạy làm gợi ý chung.
     */
    public function search(string $question, int $limit = 6): array
    {
        $tokens = $this->tokenize($question);
        $accentedBigrams = $this->accentedBigrams($question);

        if ($tokens !== []) {
            $scored = $this->catalog()
                ->map(fn (Book $book) => ['book' => $book, 'score' => $this->score($book, $tokens, $accentedBigrams)])
                ->filter(fn ($row) => $row['score'] > 0)
                ->sortByDesc('score');

            if ($scored->isNotEmpty()) {
                // Bỏ các kết quả khớp quá yếu so với kết quả tốt nhất.
                $threshold = $scored->first()['score'] * self::MIN_RELATIVE_SCORE;
                $books = $scored->filter(fn ($row) => $row['score'] >= $threshold)
                    ->take($limit)
                    ->pluck('book')
                    ->values();

                return ['books' => $books, 'matched' => true];
            }
        }

        return ['books' => $this->bestSellers($limit), 'matched' => false];
    }

    /** @return list<string> */
    public function tokenize(string $text): array
    {
        $words = preg_split('/\s+/', $this->searchable($text), -1, PREG_SPLIT_NO_EMPTY);

        // Giữ "y" vì là từ khóa của khối ngành Y.
        return array_values(array_unique(array_filter(
            $words,
            fn ($word) => (strlen($word) >= 2 || $word === 'y') && ! in_array($word, self::STOPWORDS, true)
        )));
    }

    public function normalize(string $text): string
    {
        return Str::lower(Str::ascii($text));
    }

    /** Chuỗi đã bỏ dấu, bỏ ký tự đặc biệt và các cụm chung chung, bao bởi dấu cách để so khớp nguyên từ. */
    private function searchable(string $text): string
    {
        $text = ' '.preg_replace('/[^a-z0-9+#]+/', ' ', $this->normalize($text)).' ';
        foreach (self::STOP_PHRASES as $phrase) {
            $text = str_replace(' '.$phrase.' ', ' ', $text);
        }

        return $text;
    }

    /** Chuỗi giữ nguyên dấu tiếng Việt, để phân biệt các cụm như "vĩ mô" và "vi mô". */
    private function accented(string $text): string
    {
        return ' '.trim(preg_replace('/[^\p{L}\p{N}+#]+/u', ' ', mb_strtolower($text))).' ';
    }

    /** @return list<string> */
    private function accentedBigrams(string $text): array
    {
        $words = preg_split('/\s+/', trim($this->accented($text)), -1, PREG_SPLIT_NO_EMPTY);
        $bigrams = [];
        for ($i = 0; $i < count($words) - 1; $i++) {
            $bigrams[] = $words[$i].' '.$words[$i + 1];
        }

        return $bigrams;
    }

    /**
     * @param  list<string>  $tokens
     * @param  list<string>  $accentedBigrams
     */
    private function score(Book $book, array $tokens, array $accentedBigrams): int
    {
        $fields = [
            'course' => $book->course?->name,
            'title' => $book->title,
            'major' => $book->course?->major?->name,
            'category' => $book->category?->name,
            'author' => $book->author?->name,
            'description' => $book->description,
        ];

        $score = 0;
        foreach ($fields as $field => $value) {
            if (! $value) {
                continue;
            }
            $haystack = $this->searchable($value);
            $accentedHaystack = $this->accented($value);
            foreach ($tokens as $token) {
                // Từ 2 ký tự (VD "an", "te") quá dễ trùng nên chỉ được tính khi khớp theo cụm bên dưới.
                if ($this->isMeaningfulAlone($token) && str_contains($haystack, ' '.$token.' ')) {
                    $score += self::WEIGHTS[$field];
                }
            }
            // Thưởng thêm khi khớp cả cụm hai từ liên tiếp, ví dụ "lap trinh", "kinh te".
            for ($i = 0; $i < count($tokens) - 1; $i++) {
                if (str_contains($haystack, ' '.$tokens[$i].' '.$tokens[$i + 1].' ')) {
                    $score += self::WEIGHTS[$field];
                }
            }
            // Thưởng thêm khi khớp đúng cả dấu, để "vĩ mô" không bị xếp ngang "vi mô".
            foreach ($accentedBigrams as $bigram) {
                if (str_contains($accentedHaystack, ' '.$bigram.' ')) {
                    $score += self::WEIGHTS[$field];
                }
            }
        }

        return $score;
    }

    private function isMeaningfulAlone(string $token): bool
    {
        return strlen($token) >= 3 || $token === 'y';
    }

    /** @return Collection<int, Book> */
    private function catalog(): Collection
    {
        return Book::with(['course.major', 'category', 'author'])->get();
    }

    /** @return Collection<int, Book> */
    private function bestSellers(int $limit): Collection
    {
        $soldByBook = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->groupBy('order_items.book_id')
            ->selectRaw('order_items.book_id, SUM(order_items.quantity) as sold');

        return Book::with(['course.major', 'category', 'author'])
            ->leftJoinSub($soldByBook, 'sales', 'sales.book_id', '=', 'books.id')
            ->where('books.quantity', '>', 0)
            ->orderByDesc(DB::raw('COALESCE(sales.sold, 0)'))
            ->select('books.*')
            ->limit($limit)
            ->get();
    }
}
