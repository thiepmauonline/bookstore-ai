<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Book;
use App\Models\ChatbotFeedback;
use App\Models\ChatbotHistory;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dữ liệu vận hành mẫu để demo và chụp màn hình báo cáo.
 * Chạy sau khi DatabaseSeeder đã tạo danh mục và sách.
 */
class DemoDataSeeder extends Seeder
{
    /** Số ngày trải dữ liệu đơn hàng về quá khứ. */
    private const DAYS = 30;

    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $customers = $this->seedCustomers();
        $coupons = $this->seedCoupons();
        $this->seedOrders($customers, $coupons);
        $this->seedReviews($customers);
        $this->seedWishlists($customers);
        $this->seedContacts($customers);
        $this->seedChatbot($customers);
        $this->seedLowStock();
    }

    /** @return Collection<int, User> */
    private function seedCustomers()
    {
        $people = [
            ['Lê Minh Anh', 'minhanh@bookstore.test', 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng Hậu', '144 Xuân Thủy'],
            ['Phạm Quốc Bảo', 'quocbao@bookstore.test', 'Hà Nội', 'Hai Bà Trưng', 'Bách Khoa', '1 Đại Cồ Việt'],
            ['Hoàng Thu Hà', 'thuha@bookstore.test', 'Đà Nẵng', 'Hải Châu', 'Hòa Thuận Tây', '71 Ngũ Hành Sơn'],
            ['Vũ Đức Huy', 'duchuy@bookstore.test', 'TP.HCM', 'Quận 10', 'Phường 14', '268 Lý Thường Kiệt'],
            ['Đặng Ngọc Lan', 'ngoclan@bookstore.test', 'Hà Nội', 'Đống Đa', 'Trung Liệt', '1 Tôn Thất Tùng'],
            ['Bùi Thanh Tùng', 'thanhtung@bookstore.test', 'Cần Thơ', 'Ninh Kiều', 'Xuân Khánh', '3/2 Đường 30/4'],
            ['Ngô Phương Thảo', 'phuongthao@bookstore.test', 'Huế', 'Thuận Hóa', 'Phú Nhuận', '77 Nguyễn Huệ'],
        ];

        foreach ($people as $i => [$name, $email, $province, $district, $ward, $street]) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'phone' => '09'.str_pad((string) (10000000 + $i * 1234567), 8, '0', STR_PAD_LEFT),
                'created_at' => now()->subDays(self::DAYS + 10 - $i),
            ]);
            $this->addressFor($user, $province, $district, $ward, $street);
        }

        // Tài khoản bị khóa để minh họa chức năng quản lý người dùng và đăng nhập bị từ chối.
        User::create([
            'name' => 'Tài Khoản Bị Khóa', 'email' => 'locked@bookstore.test', 'password' => Hash::make('password'),
            'role' => 'user', 'status' => 'banned', 'phone' => '0900000000',
        ]);

        $sv = User::where('email', 'sv@bookstore.test')->first();
        $kh = User::where('email', 'kh@bookstore.test')->first();
        $this->addressFor($sv, 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng', 'Ký túc xá ĐH Quốc Gia');
        $this->addressFor($kh, 'TP.HCM', 'Quận 1', 'Bến Nghé', '123 Lê Lợi');

        return User::where('role', 'user')->where('status', 'active')->with('addresses')->get();
    }

    private function addressFor(User $user, string $province, string $district, string $ward, string $street): void
    {
        Address::create([
            'user_id' => $user->id,
            'receiver_name' => $user->name,
            'phone' => $user->phone,
            'province' => $province,
            'district' => $district,
            'ward' => $ward,
            'address' => $street,
            'is_default' => true,
        ]);
    }

    /** @return array<string, Coupon> */
    private function seedCoupons(): array
    {
        $rows = [
            // Còn hiệu lực
            'CHAOTANSINHVIEN' => [30000, -20, 40, 200],
            'GIAOTRINH50K' => [50000, -10, 20, 50],
            // Đã hết hạn
            'HE2026' => [40000, -90, -30, 100],
            // Còn hạn nhưng hết lượt
            'FLASHSALE' => [100000, -5, 10, 0],
            // Chưa tới ngày áp dụng
            'KHAIGIANG' => [70000, 15, 45, 100],
        ];

        $coupons = [];
        foreach ($rows as $code => [$discount, $startOffset, $endOffset, $quantity]) {
            $coupons[$code] = Coupon::create([
                'code' => $code,
                'discount' => $discount,
                'start_date' => now()->addDays($startOffset)->startOfDay(),
                'end_date' => now()->addDays($endOffset)->endOfDay(),
                'quantity' => $quantity,
            ]);
        }

        return $coupons;
    }

    /**
     * Khoảng 45 đơn trải đều 30 ngày. Đơn cũ phần lớn đã hoàn thành, đơn mới còn đang xử lý,
     * giống vòng đời thực tế. Tồn kho được trừ tương ứng với các đơn không bị hủy.
     *
     * @param  Collection<int, User>  $customers
     * @param  array<string, Coupon>  $coupons
     */
    private function seedOrders($customers, array $coupons): void
    {
        $books = Book::all();
        $activeCoupons = [$coupons['CHAOTANSINHVIEN'], $coupons['GIAOTRINH50K']];

        for ($i = 0; $i < 45; $i++) {
            $daysAgo = (int) floor($i * self::DAYS / 45);
            $createdAt = Carbon::now()->subDays(self::DAYS - 1 - $daysAgo)->setTime(mt_rand(8, 22), mt_rand(0, 59));
            // Đơn của hôm nay không được mang giờ trong tương lai.
            if ($createdAt->isFuture()) {
                $createdAt = Carbon::now()->subMinutes(mt_rand(5, 120));
            }
            $status = $this->statusForAge(Carbon::now()->diffInDays($createdAt, true));

            $customer = $customers[mt_rand(0, $customers->count() - 1)];

            $items = [];
            $subtotal = 0;
            foreach ($books->random(mt_rand(1, 3)) as $book) {
                $qty = mt_rand(1, 2);
                $items[] = ['book' => $book, 'quantity' => $qty];
                $subtotal += $book->price * $qty;
            }

            $coupon = mt_rand(1, 100) <= 25 ? $activeCoupons[mt_rand(0, 1)] : null;
            $discount = $coupon ? min((float) $coupon->discount, $subtotal) : 0;

            $order = Order::create([
                'user_id' => $customer->id,
                'address_id' => $customer->addresses->first()?->id,
                'coupon_id' => $coupon?->id,
                'order_code' => 'DH'.$createdAt->format('ymd').strtoupper(Str::random(5)),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_price' => $subtotal - $discount,
                'payment_method' => PaymentMethod::Cod,
                'payment_status' => $status === OrderStatus::Completed ? PaymentStatus::Paid : PaymentStatus::Unpaid,
                'status' => $status,
                'note' => mt_rand(1, 100) <= 20 ? 'Giao giờ hành chính giúp mình nhé.' : null,
                'cancel_reason' => $status === OrderStatus::Cancelled ? 'Khách đặt nhầm sách' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'book_id' => $item['book']->id,
                    'price' => $item['book']->price,
                    'quantity' => $item['quantity'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                if ($status !== OrderStatus::Cancelled) {
                    $item['book']->decrement('quantity', $item['quantity']);
                }
            }

            if ($coupon && $status !== OrderStatus::Cancelled) {
                $coupon->decrement('quantity');
            }
        }
    }

    private function statusForAge(float $daysOld): OrderStatus
    {
        $roll = mt_rand(1, 100);

        if ($daysOld > 5) {
            return $roll <= 88 ? OrderStatus::Completed : OrderStatus::Cancelled;
        }

        return match (true) {
            $roll <= 30 => OrderStatus::Pending,
            $roll <= 50 => OrderStatus::Confirmed,
            $roll <= 70 => OrderStatus::Shipping,
            $roll <= 90 => OrderStatus::Completed,
            default => OrderStatus::Cancelled,
        };
    }

    /** @param Collection<int, User> $customers */
    private function seedReviews($customers): void
    {
        $comments = [
            5 => ['Sách in đẹp, nội dung đúng giáo trình trên lớp. Rất đáng mua!', 'Giao hàng nhanh, sách mới tinh. Thầy cô cũng recommend cuốn này.', 'Giải thích dễ hiểu, có nhiều ví dụ minh họa, học rất vào.'],
            4 => ['Nội dung tốt, chỉ có bìa hơi mỏng một chút.', 'Sách hay, phù hợp để ôn thi cuối kỳ.', 'Đóng gói cẩn thận, sách đúng mô tả.'],
            3 => ['Nội dung ổn nhưng hơi khó với người mới bắt đầu.', 'Sách được, nhưng giá hơi cao so với sinh viên.'],
            2 => ['Giấy in hơi mờ ở một vài trang, mong shop kiểm tra lại.'],
        ];
        $ratings = [5, 5, 5, 5, 4, 4, 4, 3, 3, 2];

        $pairs = [];
        foreach (Book::inRandomOrder()->take(18)->get() as $book) {
            foreach ($customers->random(mt_rand(1, 3)) as $customer) {
                if (isset($pairs[$customer->id.'-'.$book->id])) {
                    continue;
                }
                $pairs[$customer->id.'-'.$book->id] = true;

                $rating = $ratings[mt_rand(0, count($ratings) - 1)];
                Review::create([
                    'user_id' => $customer->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating][mt_rand(0, count($comments[$rating]) - 1)],
                    'created_at' => now()->subDays(mt_rand(0, self::DAYS)),
                ]);
            }
        }

        // Một đánh giá không phù hợp đã bị admin ẩn.
        Review::create([
            'user_id' => $customers->last()->id,
            'book_id' => Book::whereNotIn('id', Review::where('user_id', $customers->last()->id)->pluck('book_id'))->value('id'),
            'rating' => 1,
            'comment' => 'Inbox mình để mua sách giá rẻ hơn nhé!!! (spam)',
            'is_visible' => false,
        ]);
    }

    /** @param Collection<int, User> $customers */
    private function seedWishlists($customers): void
    {
        foreach ($customers as $customer) {
            foreach (Book::inRandomOrder()->take(mt_rand(1, 4))->pluck('id') as $bookId) {
                Wishlist::firstOrCreate(['user_id' => $customer->id, 'book_id' => $bookId]);
            }
        }
    }

    /** @param Collection<int, User> $customers */
    private function seedContacts($customers): void
    {
        $messages = [
            ['Cửa hàng có bán giáo trình Toán cao cấp A1 không ạ? Mình tìm mãi không thấy.', true],
            ['Cho mình hỏi đặt số lượng lớn (30 cuốn) cho cả lớp thì có giảm giá không?', false],
            ['Đơn hàng của mình giao hơi chậm, mong shop kiểm tra giúp.', true],
            ['Shop có ship ra Phú Quốc không ạ?', false],
        ];

        foreach ($messages as $i => [$message, $handled]) {
            $customer = $customers[$i % $customers->count()];
            $createdAt = now()->subDays(8 - $i * 2);
            Contact::create([
                'user_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'message' => $message,
                'is_handled' => $handled,
                'handled_at' => $handled ? $createdAt->copy()->addHours(3) : null,
                'created_at' => $createdAt,
            ]);
        }

        Contact::create([
            'name' => 'Nguyễn Hữu Phước',
            'email' => 'phuoc.nh@gmail.com',
            'message' => 'Mình là giảng viên, muốn đề xuất nhập thêm sách chuyên ngành Điện tử viễn thông.',
            'created_at' => now()->subDay(),
        ]);
    }

    /**
     * Một vài hội thoại mẫu để trang quản trị chatbot có dữ liệu hiển thị.
     *
     * @param  Collection<int, User>  $customers
     */
    private function seedChatbot($customers): void
    {
        $bookId = fn (string $title) => Book::where('title', 'like', $title.'%')->value('id');

        $conversations = [
            ['Mình học CNTT, môn Lập trình Web nên mua sách gì?', "Với học phần **Lập trình Web**, bạn có thể tham khảo:\n\n- **Lập Trình Web Căn Bản Với HTML5 & CSS3** – giáo trình tiếng Việt, phù hợp người mới.\n- **Laravel Up & Running** – khi bạn học tới phần back-end với PHP.", ['Lập Trình Web Căn Bản', 'Laravel Up'], true],
            ['Giáo trình kinh tế vĩ mô nào dễ hiểu?', "Cho môn **Kinh tế vĩ mô**, cửa hàng có:\n\n- **Kinh Tế Học Vĩ Mô (Bản Dịch Tiếng Việt)** – dễ đọc, giá mềm.\n- **Principles of Macroeconomics** – bản gốc tiếng Anh của Mankiw.", ['Kinh Tế Học Vĩ Mô', 'Principles of Macroeconomics'], true],
            ['Có sách luyện thi TOEIC không shop?', "Có bạn nhé! Hai cuốn được nhiều bạn chọn:\n\n- **ETS TOEIC Test 2024** – đề chuẩn cấu trúc mới.\n- **Barron's TOEIC Practice Exams** – nhiều đề thi thử kèm chiến lược.", ['ETS TOEIC', "Barron's TOEIC"], null],
            ['Sinh viên y năm 2 cần sách sinh lý học nào?', 'Bạn có thể bắt đầu với **Giáo Trình Sinh Lý Học Tập 1** và **Tập 2**. Nếu muốn đọc sâu hơn thì có **Guyton and Hall Textbook of Medical Physiology**.', ['Giáo Trình Sinh Lý Học Tập 1', 'Giáo Trình Sinh Lý Học Tập 2', 'Guyton'], true],
            ['Shop có bán sách nấu ăn không?', 'Hiện cửa hàng chuyên về giáo trình đại học nên chưa có sách nấu ăn. Bạn có thể hỏi mình về sách theo các ngành: Công nghệ thông tin, Quản trị kinh doanh, Y Đa Khoa, Kỹ thuật Cơ khí, Ngôn ngữ Anh nhé.', [], false],
        ];

        foreach ($conversations as $i => [$question, $answer, $titles, $helpful]) {
            $history = ChatbotHistory::create([
                'user_id' => $i % 2 === 0 ? $customers[$i % $customers->count()]->id : null,
                'session_id' => (string) Str::uuid(),
                'question' => $question,
                'answer' => $answer,
                'book_ids' => array_values(array_filter(array_map($bookId, $titles))),
                'source' => ChatbotHistory::SOURCE_AI,
                'response_ms' => mt_rand(1200, 3500),
                'created_at' => now()->subDays(6 - $i),
            ]);

            if ($helpful !== null) {
                ChatbotFeedback::create(['chatbot_history_id' => $history->id, 'is_helpful' => $helpful]);
            }
        }
    }

    /** Vài đầu sách sắp hết hàng để minh họa cảnh báo tồn kho trên dashboard. */
    private function seedLowStock(): void
    {
        Book::where('title', 'like', 'Atlas of Human Anatomy%')->update(['quantity' => 0]);
        Book::where('title', 'like', 'Advanced Grammar in Use%')->update(['quantity' => 3]);
        Book::where('title', 'like', 'Cơ Học Lý Thuyết%')->update(['quantity' => 7]);
    }
}
