<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\ChatbotHistory;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\GeminiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvaluateChatbot extends Command
{
    protected $signature = 'chatbot:evaluate {--ai : Gọi Gemini thật thay vì chế độ dự phòng} {--output= : Đường dẫn báo cáo Markdown}';

    protected $description = 'Chạy 30 tình huống chatbot và xuất câu trả lời, nguồn, thời gian, kiểm tra sách tham chiếu';

    public function handle(ChatbotService $chatbot, GeminiClient $gemini): int
    {
        if ($this->option('ai') && ! $gemini->isConfigured()) {
            $this->error('Chưa cấu hình GEMINI_API_KEY.');

            return self::FAILURE;
        }
        if (! Book::exists()) {
            $this->error('Cần có dữ liệu sách. Bộ câu hỏi kỳ vọng theo danh mục mẫu trong DatabaseSeeder.');

            return self::FAILURE;
        }

        $cases = json_decode(file_get_contents(base_path('docs/chatbot-evaluation.json')), true, flags: JSON_THROW_ON_ERROR);
        $key = config('services.gemini.key');
        if (! $this->option('ai')) {
            config(['services.gemini.key' => null]);
        }

        $rows = [];
        DB::beginTransaction();
        try {
            foreach ($cases as $case) {
                $sessionId = 'evaluation-'.Str::uuid();
                foreach ($case['context'] ?? [] as $question) {
                    $chatbot->ask($question, $sessionId);
                }
                $answer = $chatbot->ask($case['question'], $sessionId);
                $booksById = Book::whereIn('id', $answer->book_ids)->get()->keyBy('id');
                $books = collect($answer->book_ids)->map(fn ($id) => $booksById->get($id))->filter();
                $titles = $books->pluck('title');
                $passed = isset($case['no_books']) ? $titles->isEmpty()
                    : collect($case['expected_any'])->contains(fn ($expected) => $titles->contains(fn ($title) => str_contains($title, $expected)));
                if (isset($case['max_price'])) {
                    $passed = $passed && $books->every(fn ($book) => $book->price <= $case['max_price'] && $book->quantity > 0);
                }
                // Thứ tự truy hồi ổn định được kiểm tra ở chế độ nội bộ; AI có thể chọn sách khác cùng học phần.
                if (isset($case['expected_first']) && ! $this->option('ai')) {
                    $passed = $passed && collect((array) $case['expected_first'])
                        ->contains(fn ($expected) => str_contains($titles->first() ?? '', $expected));
                }
                $rows[] = compact('case', 'passed') + [
                    'answer' => $answer->answer, 'titles' => $titles->all(),
                    'source' => $answer->source, 'response_ms' => $answer->response_ms,
                ];
                $this->line(sprintf('%02d [%s] %s (%s, %d ms)', $case['id'], $passed ? 'OK' : 'CHECK', $case['question'], $answer->source, $answer->response_ms));
            }
        } finally {
            // Không đưa các hội thoại đánh giá vào lịch sử khách hàng hay thống kê admin.
            DB::rollBack();
            config(['services.gemini.key' => $key]);
        }

        $passed = collect($rows)->where('passed', true)->count();
        $aiCount = collect($rows)->where('source', ChatbotHistory::SOURCE_AI)->count();
        $mode = $this->option('ai') ? 'Gemini thật (có thể có lượt dự phòng nếu API lỗi)' : 'Tìm kiếm nội bộ / dự phòng, không gọi Gemini';
        $report = "# Kết quả đánh giá chatbot\n\n";
        $report .= 'Thời điểm: '.now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s').' (Việt Nam).'."\n\n";
        $report .= "Chế độ: {$mode}.\n\n";
        if ($this->option('ai')) {
            $report .= 'Model cấu hình: `'.config('services.gemini.model')."`.\n\n";
        }
        $report .= "Kiểm tra sách tham chiếu đạt: {$passed}/".count($rows).". Nguồn AI: {$aiCount}; dự phòng: ".(count($rows) - $aiCount).".\n\n";
        $report .= "Đây là kiểm tra sách và ngân sách theo danh mục mẫu, chưa phải điểm chất lượng ngôn ngữ hay độ chính xác toàn bộ câu trả lời. Cần đọc từng câu để chấm thủ công.\n\n";
        foreach ($rows as $row) {
            $case = $row['case'];
            $report .= "## {$case['id']}. {$case['question']}\n\n";
            if (! empty($case['context'])) {
                $report .= 'Hỏi trước: '.implode(' → ', $case['context'])."\n\n";
            }
            $report .= 'Kiểm tra sách: '.($row['passed'] ? 'Đạt' : 'Cần kiểm tra').' · Nguồn: '.$row['source'].' · '.$row['response_ms']." ms.\n\n";
            $report .= 'Sách: '.(implode('; ', $row['titles']) ?: '(không có thẻ sách)')."\n\n".$row['answer']."\n\n";
        }

        $path = $this->option('output') ?: storage_path('app/chatbot-evaluation'.($this->option('ai') ? '-ai' : '-fallback').'.md');
        if (file_put_contents($path, $report) === false) {
            $this->error('Không ghi được báo cáo: '.$path);

            return self::FAILURE;
        }
        $this->info("Kiểm tra sách: {$passed}/".count($rows).'. Báo cáo: '.$path);
        if ($this->option('ai') && $aiCount < count($rows)) {
            $this->warn('Có lượt dùng chế độ dự phòng. Xem cột nguồn trước khi sử dụng kết quả để đánh giá Gemini.');
        }

        return $passed === count($rows) && (! $this->option('ai') || $aiCount === count($rows))
            ? self::SUCCESS : self::FAILURE;
    }
}
