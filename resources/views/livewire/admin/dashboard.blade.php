<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển</h1>
            <a href="#" class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-download fa-sm text-white-50"></i> Báo cáo</a>
        </div>

        <!-- 4 Cards Thống kê -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Tổng Doanh Thu</div>
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

        <div class="row">
            <!-- Biểu đồ -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary">Biểu Đồ Doanh Thu (7 Ngày)</h6>
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
                                            <span class="d-block fw-semibold">{{ $order->user->name }}</span>
                                            <span class="text-muted small">MĐH-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="fw-bold">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                                        <td>
                                            @if($order->status == 'pending') <span class="badge bg-warning text-dark">Chờ duyệt</span> @endif
                                            @if($order->status == 'processing') <span class="badge bg-primary">Đang giao</span> @endif
                                            @if($order->status == 'completed') <span class="badge bg-success">Hoàn thành</span> @endif
                                            @if($order->status == 'cancelled') <span class="badge bg-danger">Đã hủy</span> @endif
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
