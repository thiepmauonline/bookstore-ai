# Đăng nhập khách hàng và quản trị

| Khu vực | Guard | Đăng nhập | Đăng xuất (POST + CSRF) |
| --- | --- | --- | --- |
| Khách hàng | `web` | `/login` | `/logout` |
| Quản trị | `admin` | `/admin/login` | `/admin/logout` |

Hai guard dùng chung provider `users` và bảng `users`, nhưng lưu khóa đăng nhập
và cookie ghi nhớ riêng. Client chỉ đăng nhập tài khoản `role=user`, admin chỉ
đăng nhập tài khoản `role=admin`; cả hai yêu cầu `status=active`.
Đăng ký công khai luôn tạo tài khoản khách hàng.

Guard mặc định của ứng dụng là `web`. Mọi thao tác đọc tài khoản trong admin
phải sử dụng `auth('admin')` hoặc `Auth::guard('admin')`; không đổi guard mặc định
trong middleware, tránh ảnh hưởng các component khách hàng.

`AdminMiddleware` kiểm tra guard, vai trò và trạng thái trên các route quản trị.
Middleware này được đăng ký trong danh sách persistent middleware của Livewire
để kiểm tra lại trước khi xử lý hành động trên trang admin đã mở.

Đăng xuất chỉ gọi `logout()` cho guard tương ứng và đổi session ID bằng
`migrate(true)`. Không dùng `invalidate()` hoặc `flush()`, vì sẽ xóa cả phiên
của guard còn lại và giỏ hàng. Không đổi CSRF token lúc đăng xuất để các form
đang mở của khu vực còn lại tiếp tục sử dụng được.

Đây là hai trạng thái xác thực độc lập trong cùng session Laravel, không phải
hai cookie session hoàn toàn độc lập. Đăng nhập dùng cơ chế của SessionGuard để
đổi session ID và CSRF token; form mở từ trước lần đăng nhập mới có thể cần tải
lại. Session hết hạn hoặc xóa cookie session ảnh hưởng cả hai guard. Cookie ghi
nhớ của mỗi guard được Laravel quản lý riêng.

Giới hạn đăng nhập: 5 lần thất bại trong 60 giây, theo guard + email + IP.
Thành công xóa bộ đếm của guard tương ứng. Không cần migration hay seed lại.
Sau khi triển khai thay đổi guard, xóa cache cấu hình/route/view cũ bằng các lệnh
`php artisan config:clear`, `php artisan route:clear`, `php artisan view:clear`.
Admin đang đăng nhập qua guard cũ cần đăng nhập lại tại `/admin/login`.

Kiểm thử: `php artisan test --filter=SeparateGuardsTest`.
