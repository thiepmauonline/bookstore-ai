<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\User;
use App\Models\Book;
use Carbon\Carbon;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        // Thống kê cơ bản
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $totalUsers = User::where('role', 'user')->count();
        $totalBooks = Book::count();

        // 5 đơn hàng mới nhất
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        // Dữ liệu biểu đồ doanh thu (7 ngày gần nhất)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_price');
                
            $chartData['labels'][] = $date->format('d/m');
            $chartData['data'][] = $revenue;
        }

        return view('livewire.admin.dashboard', compact(
            'totalRevenue', 'totalOrders', 'totalUsers', 'totalBooks', 'recentOrders', 'chartData'
        ));
    }
}
