# Đánh giá chatbot cho đồ án

Bộ [30 tình huống](chatbot-evaluation.json) gồm chào hỏi, tìm theo tên/ngành/học phần, tiếng Việt không dấu, phân biệt vi mô/vĩ mô, ngân sách và câu hỏi tiếp nối. Các tên sách và mức giá kỳ vọng dựa trên danh mục mẫu của `DatabaseSeeder`; nếu đổi dữ liệu, cần cập nhật kỳ vọng trước khi chấm.

## Chạy và lấy kết quả

Chạy tìm kiếm nội bộ, không gọi API:

```bash
php artisan chatbot:evaluate
```

Chạy Gemini thật với cấu hình hiện tại trong `.env`:

```bash
php artisan chatbot:evaluate --ai
```

Mặc định báo cáo nằm ở `storage/app/chatbot-evaluation-fallback.md` hoặc `storage/app/chatbot-evaluation-ai.md`. Dùng `--output=docs/ten-bao-cao.md` để chọn nơi lưu. Thư mục đích cần tồn tại.

Mỗi tình huống có một phiên riêng; các câu ở trường `context` được hỏi trước trong cùng phiên. Vì vậy 30 tình huống có thể gọi API nhiều hơn 30 lần. Hội thoại chạy thử nằm trong transaction và được rollback, không lưu vào lịch sử khách hàng hay thống kê admin. Không cần migrate hoặc seed lại database để chạy.

Lệnh kiểm tra có thẻ sách kỳ vọng, mức giá và tồn kho đối với câu có ngân sách; với ca cần hỏi thêm thông tin thì kiểm tra không gắn thẻ sách. Khi chạy `--ai`, nếu bất kỳ câu trả lời chính nào phải chuyển sang dự phòng, lệnh trả mã lỗi và báo rõ số lượt AI/dự phòng.

## Kết quả đã chạy ngày 16/10/2026

- [Báo cáo tìm kiếm nội bộ](chatbot-evaluation-fallback.md): đạt 30/30 kiểm tra sách tham chiếu và ngân sách.
- [Báo cáo thử gọi Gemini](chatbot-evaluation-ai.md): cả 30 câu trả lời chính đều dùng dự phòng. API trả HTTP 401 với thông báo API key không hợp lệ. Kết quả này kiểm chứng chế độ dự phòng, chưa đánh giá được chất lượng Gemini thật.

Sau khi thay bằng API key hợp lệ, chạy lại `php artisan chatbot:evaluate --ai` để lấy kết quả AI. Không đưa API key vào báo cáo hoặc ảnh chụp.

## Chấm thủ công cho báo cáo tốt nghiệp

Đọc câu trả lời thực tế trong báo cáo và ghi các tiêu chí sau cho mỗi câu:

| Tiêu chí | Cách chấm |
| --- | --- |
| Đúng nhu cầu | 1 nếu đúng học phần/chủ đề; 0 nếu lạc chủ đề |
| Đúng dữ liệu | 1 nếu tên sách, giá và tình trạng hàng khớp CSDL; 0 nếu có thông tin bịa |
| Đúng ngữ cảnh | 1 nếu hiểu câu hỏi tiếp nối hoặc hỏi lại khi thiếu thông tin; 0 nếu tự đoán sai |
| Dễ hiểu | 1 nếu trả lời rõ, ngắn và có ích; 0 nếu khó hiểu |
| Thời gian | Ghi `response_ms`; API lỗi có thể làm tăng thời gian do timeout/retry |

Tính tỷ lệ đạt riêng cho từng tiêu chí và tách nguồn `ai`/`fallback`. Con số 30/30 của lệnh chỉ là kiểm tra sách tham chiếu, không phải độ chính xác toàn bộ AI. Với phản hồi AI, thứ tự sách có thể khác thứ tự truy hồi; cần đọc nội dung để xác nhận tư vấn đúng.

## Phạm vi xử lý câu hỏi tiếp nối

Chatbot dùng các sách được gợi ý trong ba lượt gần nhất của cùng phiên để tìm tiếp theo học phần. Ví dụ: “Tìm sách Laravel” → “Có cuốn nào rẻ hơn không?” hoặc “Ngân sách của mình là 200k”. Nếu thiếu ngữ cảnh, không còn sách phù hợp hoặc hết hàng trong khoảng giá, hệ thống hỏi thêm thay vì đưa sách bán chạy khác ngành.

Ngân sách hỗ trợ dạng `200k`, `200 nghìn`, `200.000đ`, `200000 VND`, `0,3 triệu` và các cụm “dưới”, “tối đa”, “không quá”, “ngân sách”. Giới hạn giá được xét là mức tối đa, bao gồm giá bằng giới hạn. Chưa xử lý khoảng giá hai đầu hoặc bảo đảm mức độ khó của sách; các tiêu chí này cần mô tả dữ liệu cụ thể hơn.
