# Kiểm tra hồi quy ngày 16/10/2026

## Kiểm thử tự động

`php artisan test --compact`: **62 test đạt, 337 assertions** sau bản sửa giao diện cuối cùng.

Bao gồm đăng nhập hai guard, phân quyền, khóa tài khoản khi đang đăng nhập, giỏ hàng, tồn kho, mã giảm giá, đặt/hủy/chuyển trạng thái đơn, đánh giá, chatbot và lệnh đánh giá 30 tình huống.

## Kiểm tra bằng trình duyệt

Chrome headless chạy qua Playwright, viewport 1440 × 1000, dùng máy chủ localhost và SQLite riêng trong `/tmp`. Các thao tác tạo đơn, đánh giá và khóa tài khoản đều dùng dữ liệu thử, không dùng MySQL hiện tại.

| Nhóm kiểm tra | Kết quả |
| --- | --- |
| 8 trang công khai, Livewire khởi động | Đạt |
| Đăng nhập khách hàng | Đạt |
| Tìm kiếm danh mục và cập nhật kết quả trực tiếp | Đạt |
| Thêm/xóa sách yêu thích | Đạt |
| Thêm sách từ trang chi tiết vào giỏ | Đạt |
| Tăng/giảm số lượng trong giỏ | Đạt |
| Từ chối mã không tồn tại, áp dụng mã hợp lệ | Đạt |
| Điền giao hàng và đặt đơn COD | Đạt |
| Mở modal chi tiết đơn và tự hủy đơn | Đạt |
| Chatbot tìm sách, hỏi rẻ hơn, bắt đầu phiên mới | Đạt ở chế độ dự phòng |
| Đăng nhập admin đồng thời vẫn giữ đăng nhập khách | Đạt |
| 14 trang quản trị tải và khởi động JavaScript | Đạt |
| Chuyển trang đánh giá, gửi đánh giá mới và giữ tab đang mở | Đạt |
| Khóa khách từ admin khi checkout đang mở: chặn đặt hàng, giữ phiên admin | Đạt |

Không ghi nhận lỗi JavaScript hoặc phản hồi HTTP từ 400 trở lên trên máy chủ thử trong các ca trình duyệt đã đạt. Việc tải trang quản trị không thay thế kiểm thử toàn bộ thao tác CRUD; các nghiệp vụ được bao phủ thêm bằng bộ test PHP ở trên.

## Lỗi phát hiện và đã sửa

Khi chuyển trang đánh giá, dữ liệu được tải đúng nhưng Livewire ghi đè trạng thái tab do Bootstrap quản lý, làm tab quay về Mô tả. Thêm `wire:ignore.self` cho nút tab và khung nội dung để giữ trạng thái tab trong khi nội dung đánh giá vẫn cập nhật. Đã kiểm chứng lại bằng trình duyệt cả phân trang và gửi đánh giá khi đang ở trang 2.

## Giới hạn

Kiểm tra lại `php artisan chatbot:check`: Gemini vẫn trả HTTP 401. Vì vậy chưa kiểm chứng được chất lượng câu trả lời Gemini thật; chế độ dự phòng và các nhánh API được kiểm tra bằng dữ liệu nội bộ/giả lập.

Các kết quả trên áp dụng cho phạm vi đã kiểm tra, không phải cam kết không còn lỗi ở mọi thiết bị, mọi thao tác CRUD hoặc tình huống truy cập đồng thời.
