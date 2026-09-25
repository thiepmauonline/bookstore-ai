# Checklist hoàn thiện đồ án Bookstore-AI

Quyết định phạm vi (25/09/2026): **không** thêm ràng buộc khóa ngoại, **không** tích hợp VNPay/MoMo, chỉ thanh toán khi nhận hàng (COD).

## 1. Chức năng

- [x] **Chatbot AI tư vấn giáo trình** (Google Gemini, có chế độ dự phòng khi không gọi được AI)
  - [x] `BookRetriever`: tìm sách theo từ khóa, không phân biệt dấu, có trọng số theo trường
  - [x] `ChatbotService`: đưa sách liên quan + danh mục ngành/học phần vào prompt, lọc mã `[#id]`
  - [x] Khung chat nổi phía khách, câu hỏi gợi ý, thẻ sách, nút hữu ích / không hữu ích
  - [x] Trang admin "Dữ liệu AI Chatbot": lịch sử, tỉ lệ hữu ích, sách được gợi ý nhiều nhất
- [x] Thanh toán: chỉ COD (đã bỏ lựa chọn VNPay/MoMo)
- [x] Trang admin "Cài đặt": tên cửa hàng, hotline, email, địa chỉ, giờ làm việc
- [x] Admin quản lý đánh giá: lọc, ẩn/hiện, xóa
- [x] Form liên hệ lưu vào DB + trang admin "Liên hệ" (đánh dấu đã xử lý)
- [x] Khách tự hủy đơn khi còn "Chờ xác nhận"
- [x] Admin khóa/mở khóa tài khoản khách; không cho xóa khách đã có đơn
- [x] Gợi ý sách liên quan ở trang chi tiết (ưu tiên cùng học phần)
- [x] Dashboard: doanh thu 30 ngày, đơn theo trạng thái, top sách bán chạy, sắp hết hàng
- [ ] *(Chưa làm)* Upload nhiều ảnh cho sách: bảng `book_images` đã có nhưng trang quản lý sách chưa dùng

## 2. Database

- [x] Chuẩn hóa trạng thái bằng PHP Enum (`app/Enums`)
  - `status`: `pending → confirmed → shipping → completed`, `cancelled`
  - `payment_status`: `unpaid`, `paid` · `payment_method`: `cod`
- [x] `orders` thêm `subtotal`, `discount_amount`, `cancel_reason`
- [x] `unique(user_id, book_id)` cho `reviews` và `wishlists`; `reviews.is_visible`
- [x] `chatbot_histories` thêm `session_id`, `book_ids`, `source`, `response_ms`; `chatbot_feedback` unique theo câu trả lời
- [x] Bảng mới: `contacts`, `settings`

## 3. Nghiệp vụ

- [x] `OrderService`: đặt hàng, chuyển trạng thái, hủy đơn trong transaction + `lockForUpdate()`
- [x] Tính tiền theo giá trong CSDL, không tin giá lưu trong session
- [x] Hủy đơn hoàn tồn kho và hoàn lượt mã giảm giá
- [x] Chỉ cho chuyển trạng thái theo đúng luồng; admin hủy đơn phải nhập lý do

## 4. Dữ liệu mẫu

- [x] 10 khách hàng (1 tài khoản bị khóa), 5 mã giảm giá đủ các tình huống
- [x] 45 đơn hàng trải 30 ngày, đủ 5 trạng thái, tồn kho trừ tương ứng
- [x] ~40 đánh giá (1 đánh giá bị ẩn), yêu thích, liên hệ, hội thoại chatbot
- [x] Vài sách sắp hết hàng để demo cảnh báo

## 5. Dọn dẹp

- [x] Sửa `ExampleTest`; thêm test mới, tổng 37 test đều đạt
- [x] Thông báo validation tiếng Việt (`lang/vi/validation.php`, `APP_LOCALE=vi`)
- [x] Viết lại `README.md`
- [x] Xóa script tạm ở gốc và thư mục `theme/adminhmd-1.0.0/.vs/`
- [ ] Commit theo từng phần việc

## 6. Chuẩn bị cho báo cáo

- [ ] Lấy Gemini API key, thử chatbot thật, ghi lại 10 câu hỏi mẫu + câu trả lời
- [ ] Khảo sát Google Form 20–30 sinh viên (Chương 1)
- [ ] Bảng so sánh với Fahasa / Tiki / nhà sách trường (Chương 1)
- [ ] Sơ đồ: Use Case, Activity, Sequence, State (đơn hàng), Class, ERD, Deployment
- [ ] Chụp 12–15 màn hình (`php artisan migrate:fresh --seed` trước khi chụp)
- [ ] Bảng kiểm thử thủ công 20–30 ca + ảnh chụp `php artisan test`
