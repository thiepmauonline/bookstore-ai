# Bookstore AI

Website bán giáo trình đại học có tích hợp **Chatbot AI tư vấn tài liệu theo ngành và học phần**.

Sinh viên tìm sách theo mô hình **Ngành → Học phần → Sách**, đặt hàng và thanh toán khi nhận hàng (COD), hoặc hỏi chatbot để được gợi ý giáo trình phù hợp. Quản trị viên quản lý sách, đơn hàng, mã giảm giá và theo dõi dữ liệu hội thoại của chatbot.

## Chức năng

**Khách hàng**
- Đăng ký, đăng nhập (giới hạn số lần đăng nhập sai)
- Tìm kiếm, lọc sách theo danh mục, ngành, học phần; sắp xếp theo giá
- Xem chi tiết sách, sách liên quan (ưu tiên cùng học phần), đánh giá 1–5 sao
- Giỏ hàng, thanh toán COD, áp mã giảm giá
- Theo dõi đơn hàng, tự hủy đơn khi đơn còn "Chờ xác nhận"
- Danh sách yêu thích
- **Chatbot tư vấn giáo trình**, đánh giá câu trả lời hữu ích / không hữu ích
- Gửi liên hệ cho cửa hàng

**Quản trị viên**
- Dashboard: doanh thu 30 ngày, đơn hàng theo trạng thái, top sách bán chạy, sách sắp hết hàng
- Quản lý sách, danh mục, tác giả, nhà xuất bản, ngành & học phần
- Quản lý đơn hàng theo đúng luồng trạng thái, hủy đơn có lý do
- Quản lý mã giảm giá, người dùng (khóa/mở tài khoản), đánh giá (ẩn/xóa)
- Dữ liệu AI Chatbot: lịch sử hỏi đáp, tỉ lệ hữu ích, sách được gợi ý nhiều nhất
- Liên hệ từ khách hàng, cài đặt thông tin cửa hàng

## Công nghệ

| Thành phần | Công nghệ |
|---|---|
| Backend | PHP 8.2, Laravel 12, Livewire 3 |
| Frontend | Blade, Bootstrap 5.3, Bootstrap Icons, Alpine.js (đi kèm Livewire), Chart.js |
| CSDL | MySQL |
| AI | Google Gemini API (gói miễn phí) |
| Kiểm thử | PHPUnit |

## Cài đặt

Yêu cầu: PHP ≥ 8.2, Composer, MySQL, Node.js (tùy chọn, chỉ cần khi build asset bằng Vite).

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Tạo database `bookstore-ai` trong MySQL, sửa các biến `DB_*` trong `.env`, rồi chạy:

```bash
php artisan migrate:fresh --seed
php artisan storage:link   # để hiển thị ảnh bìa sách tải lên từ trang quản trị
php artisan serve
```

Truy cập http://127.0.0.1:8000 (khách hàng) và http://127.0.0.1:8000/admin (quản trị).

### Tài khoản demo

Tất cả tài khoản dùng mật khẩu `password`.

| Vai trò | Email |
|---|---|
| Quản trị viên | `admin@bookstore.test` |
| Khách hàng | `sv@bookstore.test`, `kh@bookstore.test`, `minhanh@bookstore.test`... |
| Tài khoản bị khóa | `locked@bookstore.test` |

Mã giảm giá mẫu: `CHAOTANSINHVIEN`, `GIAOTRINH50K` (còn hiệu lực), `HE2026` (hết hạn), `FLASHSALE` (hết lượt), `KHAIGIANG` (chưa tới ngày).

### Cấu hình Chatbot AI

1. Lấy API key miễn phí tại https://aistudio.google.com/apikey
2. Thêm vào `.env`:
   ```
   GEMINI_API_KEY=your-key
   GEMINI_MODEL=gemini-flash-latest
   ```
3. Chạy `php artisan config:clear`
4. Kiểm tra kết nối: `php artisan chatbot:check`. Lệnh báo thành công và in ra một câu trả lời thử.

Khi chưa có key, hoặc khi API lỗi hay hết hạn mức, chatbot tự chuyển sang **chế độ dự phòng**: trả lời bằng kết quả tìm kiếm sách trong CSDL, nên chức năng không bị gián đoạn. Trang *Admin → Dữ liệu AI Chatbot* cho biết trạng thái cấu hình và nguồn của từng câu trả lời.

## Kiến trúc

```
app/
├── Enums/              OrderStatus, PaymentStatus, PaymentMethod (trạng thái + nhãn tiếng Việt)
├── Services/
│   ├── OrderService    Đặt hàng, chuyển trạng thái, hủy đơn (transaction + khóa dòng)
│   ├── CartService     Giỏ hàng lưu trong session
│   └── Chatbot/        BookRetriever (tìm sách), GeminiClient (gọi API), ChatbotService (điều phối)
├── Livewire/
│   ├── Client/         Các trang phía khách hàng + khung chat
│   └── Admin/          Các trang quản trị
└── Models/             Eloquent models
```

**Luồng trạng thái đơn hàng**

```
pending ──► confirmed ──► shipping ──► completed (tự chuyển "Đã thanh toán")
   │            │            │
   └────────────┴────────────┴──► cancelled (hoàn tồn kho + hoàn lượt mã giảm giá)
```

Khách chỉ tự hủy được khi đơn ở trạng thái `pending`. Quản trị viên hủy đơn phải nhập lý do.

**Chatbot (RAG đơn giản)**

1. `BookRetriever` tìm sách liên quan theo từ khóa, không phân biệt dấu, có trọng số (học phần > tên sách > ngành > danh mục > mô tả).
2. `ChatbotService` đưa danh sách sách, danh mục ngành/học phần và thông tin cửa hàng vào ngữ cảnh gửi cho Gemini. AI chỉ được gợi ý sách có trong danh sách và đánh dấu bằng `[#id]`.
3. Hệ thống tách các mã `[#id]` (chỉ giữ những id có trong ngữ cảnh), hiển thị thẻ sách bên dưới câu trả lời và lưu lịch sử vào `chatbot_histories`.

Chi tiết về đăng nhập hai khu vực: [docs/authentication.md](docs/authentication.md).

## Kiểm thử

```bash
php artisan test
```

Bộ test gồm 43 test, bao phủ: đặt hàng, giỏ hàng, tồn kho, mã giảm giá, luồng trạng thái, hủy đơn, chatbot (có giả lập API Gemini), đánh giá, liên hệ, cài đặt, khóa tài khoản, toàn vẹn dữ liệu khi xóa và tách phiên đăng nhập admin/khách hàng.
