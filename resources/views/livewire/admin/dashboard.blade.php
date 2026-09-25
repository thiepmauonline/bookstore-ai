<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển</h1>
        </div>

        <!-- 4 Cards Thống kê -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Doanh thu (đơn hoàn thành)</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="col-auto text-muted fs-2">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-start border-success border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                    Số Đơn Hàng</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalOrders }}</div>
                            </div>
                            <div class="col-auto text-muted fs-2">
                                <i class="bi bi-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-start border-info border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Số Khách Hàng
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalUsers }}</div>
                            </div>
                            <div class="col-auto text-muted fs-2">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                    Số Đầu Sách</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalBooks }}</div>
                            </div>
                            <div class="col-auto text-muted fs-2">
                                <i class="bi bi-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng theo trạng thái + cảnh báo -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center">
                <span class="fw-bold text-primary me-2">Đơn hàng theo trạng thái:</span>
                @foreach ($statuses as $status)
                    <a href="{{ route('admin.orders', ['statusFilter' => $status->value]) }}" class="text-decoration-none">
                        <span class="badge {{ $status->badgeClass() }} fs-6">{{ $status->label() }}: {{ $ordersByStatus[$status->value] ?? 0 }}</span>
                    </a>
                @endforeach
                <span class="ms-auto d-flex gap-3 small">
                    <a href="{{ route('admin.contacts') }}" class="text-decoration-none"><i class="bi bi-envelope"></i> {{ $unhandledContacts }} liên hệ chưa xử lý</a>
                    <a href="{{ route('admin.chatbot') }}" class="text-decoration-none"><i class="bi bi-robot"></i> Chatbot hữu ích: {{ $chatbotHelpfulRate !== null ? $chatbotHelpfulRate.'%' : 'chưa có dữ liệu' }}</a>
                </span>
            </div>
        </div>

        <div class="row">
            <!-- Biểu đồ -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Biểu Đồ Doanh Thu ({{ $chartDays }} ngày gần nhất)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height: 320px;">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng Đơn Hàng Mới Nhất -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Đơn Hàng Mới Nhất</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-striped table-vcenter mb-0">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="d-block fw-semibold">{{ $order->user->name ?? 'N/A' }}</span>
                                            <span class="text-muted small">{{ $order->order_code }}</span>
                                        </td>
                                        <td class="fw-bold">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                                        <td>
                                            <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 text-center border-top">
                            <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-primary">Xem tất cả đơn hàng</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header py-3 bg-white"><h6 class="m-0 fw-bold text-primary">Top 5 sách bán chạy</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th class="ps-3">Sách</th><th class="text-center">Đã bán</th><th class="text-end pe-3">Doanh thu</th></tr></thead>
                            <tbody>
                                @forelse ($topBooks as $book)
                                    <tr>
                                        <td class="ps-3">{{ $book->title }}</td>
                                        <td class="text-center fw-semibold">{{ $book->sold }}</td>
                                        <td class="text-end pe-3">{{ number_format($book->revenue, 0, ',', '.') }}đ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Chưa có đơn hoàn thành</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header py-3 bg-white"><h6 class="m-0 fw-bold text-danger">Sắp hết hàng (dưới {{ $lowStockThreshold }} cuốn)</h6></div>
                    <ul class="list-group list-group-flush">
                        @forelse ($lowStockBooks as $book)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-truncate me-2">{{ $book->title }}</span>
                                <span class="badge {{ $book->quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $book->quantity }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">Tồn kho ổn định</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tải Chart.js từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('myAreaChart');
            if(ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartData['labels']) !!},
                        datasets: [{
                            label: "Doanh thu (VNĐ)",
                            lineTension: 0.3,
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            borderColor: "rgba(78, 115, 223, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointBorderColor: "rgba(78, 115, 223, 1)",
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: {!! json_encode($chartData['data']) !!},
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                        scales: {
                            x: { grid: { display: false, drawBorder: false } },
                            y: {
                                ticks: {
                                    maxTicksLimit: 5,
                                    padding: 10,
                                    callback: function(value, index, values) {
                                        return value.toLocaleString('vi-VN') + 'đ';
                                    }
                                },
                                grid: {
                                    color: "rgb(234, 236, 244)",
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
</div>
