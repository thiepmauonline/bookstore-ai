<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Dữ liệu AI Chatbot</h1>
                    <p class="text-muted mb-0">
                        Mô hình: <code>{{ $aiModel }}</code> ·
                        @if ($aiConfigured)
                            <span class="badge bg-success">Đã cấu hình API key</span>
                        @else
                            <span class="badge bg-warning text-dark">Chưa có GEMINI_API_KEY, chatbot đang trả lời bằng tìm kiếm nội bộ</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Câu hỏi</div>
                    <div class="h4 mb-0">{{ $stats['questions'] }}</div>
                    <div class="small text-muted">{{ $stats['sessions'] }} phiên trò chuyện</div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Tỉ lệ hữu ích</div>
                    <div class="h4 mb-0 text-success">{{ $stats['helpfulRate'] !== null ? $stats['helpfulRate'].'%' : '—' }}</div>
                    <div class="small text-muted">trên {{ $stats['feedbackTotal'] }} lượt phản hồi</div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Trả lời dự phòng</div>
                    <div class="h4 mb-0 text-warning">{{ $stats['fallback'] }}</div>
                    <div class="small text-muted">lượt không gọi được AI</div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Thời gian phản hồi AI</div>
                    <div class="h4 mb-0">{{ $stats['avgMs'] ? number_format($stats['avgMs'] / 1000, 1).' giây' : '—' }}</div>
                    <div class="small text-muted">trung bình</div>
                </div></div>
            </div>
        </div>

        @if ($topBooks->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <span class="fw-bold text-primary me-2">Sách được gợi ý nhiều nhất:</span>
                    @foreach ($topBooks as $title => $count)
                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $title }} <span class="text-muted">× {{ $count }}</span></span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm trong câu hỏi, câu trả lời...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="feedbackFilter" class="form-select bg-light">
                            <option value="">-- Mọi phản hồi --</option>
                            <option value="helpful">Hữu ích</option>
                            <option value="unhelpful">Không hữu ích</option>
                            <option value="none">Chưa phản hồi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="sourceFilter" class="form-select bg-light">
                            <option value="">-- Mọi nguồn trả lời --</option>
                            <option value="ai">AI (Gemini)</option>
                            <option value="fallback">Dự phòng (tìm kiếm nội bộ)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 30%">Câu hỏi</th>
                            <th style="width: 35%">Trả lời</th>
                            <th>Người hỏi</th>
                            <th>Nguồn</th>
                            <th>Phản hồi</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($histories as $history)
                            <tr wire:key="history-{{ $history->id }}">
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ Str::limit($history->question, 90) }}</div>
                                    <div class="small text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="small text-muted">{{ Str::limit(strip_tags($history->answer), 120) }}</td>
                                <td class="small">{{ $history->user->name ?? 'Khách vãng lai' }}</td>
                                <td>
                                    @if ($history->source === \App\Models\ChatbotHistory::SOURCE_AI)
                                        <span class="badge bg-primary">AI</span>
                                    @else
                                        <span class="badge bg-secondary">Dự phòng</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($history->feedback === null)
                                        <span class="text-muted small">—</span>
                                    @elseif ($history->feedback->is_helpful)
                                        <i class="bi bi-hand-thumbs-up-fill text-success" title="Hữu ích"></i>
                                    @else
                                        <i class="bi bi-hand-thumbs-down-fill text-danger" title="Không hữu ích"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button wire:click="showDetails({{ $history->id }})" class="btn btn-sm btn-outline-primary me-1" title="Xem"><i class="bi bi-eye"></i></button>
                                    <button wire:click="delete({{ $history->id }})" wire:confirm="Xóa lượt hỏi đáp này?" class="btn btn-sm btn-outline-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu hội thoại</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $histories->links() }}</div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="chatbotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                @if ($viewing)
                    <div class="modal-header">
                        <h5 class="modal-title">Chi tiết hỏi đáp #{{ $viewing->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">
                            {{ $viewing->created_at->format('d/m/Y H:i:s') }} · {{ $viewing->user->name ?? 'Khách vãng lai' }}
                            · Nguồn: {{ $viewing->source === 'ai' ? 'AI (Gemini)' : 'Dự phòng' }}
                            @if ($viewing->response_ms) · {{ number_format($viewing->response_ms) }} ms @endif
                        </p>
                        <h6 class="fw-bold">Câu hỏi</h6>
                        <div class="p-3 bg-light rounded mb-3">{{ $viewing->question }}</div>
                        <h6 class="fw-bold">Câu trả lời</h6>
                        <div class="p-3 border rounded">{!! Str::markdown($viewing->answer, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                        @if (! empty($viewing->book_ids))
                            <h6 class="fw-bold mt-3">Sách được gợi ý</h6>
                            <ul class="mb-0">
                                @foreach (\App\Models\Book::whereIn('id', $viewing->book_ids)->get() as $book)
                                    <li><a href="{{ route('book.detail', $book->id) }}" target="_blank">{{ $book->title }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const modal = new bootstrap.Modal(document.getElementById('chatbotModal'));
            Livewire.on('show-modal', () => modal.show());
        });
    </script>
</div>
