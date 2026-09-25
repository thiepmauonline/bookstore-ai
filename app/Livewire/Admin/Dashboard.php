<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\ChatbotFeedback;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    /** Số ngày hiển thị trên biểu đồ doanh thu. */
    private const CHART_DAYS = 30;

    /** Ngưỡng cảnh báo sắp hết hàng. */
    private const LOW_STOCK = 10;

    public function render()
    {
        $completed = Order::where('status', OrderStatus::Completed);

        $ordersByStatus = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topBooks = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->selectRaw('books.id, books.title, SUM(order_items.quantity) as sold, SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('books.id', 'books.title')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        $feedbackTotal = ChatbotFeedback::count();
        $chatbotHelpfulRate = $feedbackTotal
            ? round(ChatbotFeedback::where('is_helpful', true)->count() * 100 / $feedbackTotal)
            : null;

        return view('livewire.admin.dashboard', [
            'totalRevenue' => (clone $completed)->sum('total_price'),
            'totalOrders' => Order::count(),
            'totalUsers' => User::where('role', 'user')->count(),
            'totalBooks' => Book::count(),
            'ordersByStatus' => $ordersByStatus,
            'statuses' => OrderStatus::cases(),
            'recentOrders' => Order::with('user')->latest()->take(6)->get(),
            'topBooks' => $topBooks,
            'lowStockBooks' => Book::where('quantity', '<', self::LOW_STOCK)->orderBy('quantity')->take(5)->get(),
            'unhandledContacts' => Contact::where('is_handled', false)->count(),
            'chatbotHelpfulRate' => $chatbotHelpfulRate,
            'chartData' => $this->revenueChart(),
            'chartDays' => self::CHART_DAYS,
            'lowStockThreshold' => self::LOW_STOCK,
        ]);
    }

    /**
     * Doanh thu theo ngày của các đơn đã hoàn thành, gộp trong một truy vấn.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    private function revenueChart(): array
    {
        $from = Carbon::today()->subDays(self::CHART_DAYS - 1);

        $revenueByDay = Order::where('status', OrderStatus::Completed)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(total_price) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $chart = ['labels' => [], 'data' => []];
        for ($date = $from->copy(); $date->lte(Carbon::today()); $date->addDay()) {
            $chart['labels'][] = $date->format('d/m');
            $chart['data'][] = (float) ($revenueByDay[$date->toDateString()] ?? 0);
        }

        return $chart;
    }
}
