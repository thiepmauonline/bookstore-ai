# Ảnh sách demo

Bộ ảnh được tạo bằng công cụ imagegen tích hợp, theo phong cách chụp sách thật.
Đây là thiết kế bìa minh họa cho demo, không phải ảnh chụp các ấn bản thương mại.
Mỗi đầu sách trong `DatabaseSeeder` có một ảnh riêng trong
`public/assets/client/images/books/demo/`, đánh số theo thứ tự sách trong seeder.

Danh sách tên sách, ý tưởng thiết kế và prompt nằm trong `book-image-prompts.json`.
Bảng ánh xạ tên sách sang ảnh nằm trong `database/data/book-covers.json`.

`DatabaseSeeder` sử dụng trực tiếp các đường dẫn mới khi tạo dữ liệu demo.
Với cơ sở dữ liệu đã có sách demo, chỉ cập nhật ảnh bằng:

```bash
php artisan db:seed --class=BookCoverSeeder
```

Lệnh đối chiếu chính xác tên sách, kiểm tra đủ file ảnh trước khi cập nhật,
và thực hiện cập nhật trong một transaction. Các sách khác không bị ảnh hưởng.
