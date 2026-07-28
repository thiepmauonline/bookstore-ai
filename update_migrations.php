<?php
$dir = __DIR__ . '/database/migrations/';

function updateMigration($dir, $table, $schemaContent) {
    $files = glob($dir . '*_create_' . $table . '_table.php');
    if (!$files) {
        echo "Not found for $table\n";
        return;
    }
    $file = $files[0];
    
    $content = file_get_contents($file);
    // Replace the Schema::create part
    $pattern = '/Schema::create\(\'' . preg_quote($table) . '\', function \(Blueprint \$table\) \{.*?\}\);/s';
    
    $replacement = "Schema::create('$table', function (Blueprint \$table) {\n$schemaContent\n        });";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    file_put_contents($file, $newContent);
    echo "Updated $file\n";
}

// 2. Addresses
$addresses = <<<EOT
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID người dùng');
            \$table->string('receiver_name')->comment('Tên người nhận');
            \$table->string('phone')->comment('Số điện thoại nhận hàng');
            \$table->string('province')->comment('Tỉnh/Thành phố');
            \$table->string('district')->comment('Quận/Huyện');
            \$table->string('ward')->comment('Phường/Xã');
            \$table->text('address')->comment('Địa chỉ chi tiết');
            \$table->boolean('is_default')->default(false)->comment('Là địa chỉ mặc định');
            \$table->timestamps();
EOT;
updateMigration($dir, 'addresses', $addresses);

// 3. Categories
$categories = <<<EOT
            \$table->id();
            \$table->string('name')->comment('Tên danh mục');
            \$table->string('slug')->unique()->comment('Đường dẫn SEO');
            \$table->timestamps();
EOT;
updateMigration($dir, 'categories', $categories);

// 4. Authors
$authors = <<<EOT
            \$table->id();
            \$table->string('name')->comment('Tên tác giả');
            \$table->text('biography')->nullable()->comment('Tiểu sử tác giả');
            \$table->timestamps();
EOT;
updateMigration($dir, 'authors', $authors);

// 5. Publishers
$publishers = <<<EOT
            \$table->id();
            \$table->string('name')->comment('Tên nhà xuất bản');
            \$table->string('address')->nullable()->comment('Địa chỉ NXB');
            \$table->timestamps();
EOT;
updateMigration($dir, 'publishers', $publishers);

// 6. Majors
$majors = <<<EOT
            \$table->id();
            \$table->string('name')->comment('Tên ngành học (VD: CNTT, Kinh tế)');
            \$table->timestamps();
EOT;
updateMigration($dir, 'majors', $majors);

// 7. Courses
$courses = <<<EOT
            \$table->id();
            \$table->foreignId('major_id')->constrained()->cascadeOnDelete()->comment('ID ngành học');
            \$table->string('name')->comment('Tên học phần (VD: Lập trình Web)');
            \$table->timestamps();
EOT;
updateMigration($dir, 'courses', $courses);

// 8. Books
$books = <<<EOT
            \$table->id();
            \$table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete()->comment('ID danh mục');
            \$table->foreignId('author_id')->nullable()->constrained()->restrictOnDelete()->comment('ID tác giả');
            \$table->foreignId('publisher_id')->nullable()->constrained()->restrictOnDelete()->comment('ID nhà xuất bản');
            \$table->foreignId('course_id')->nullable()->constrained()->restrictOnDelete()->comment('ID học phần');
            
            \$table->string('title')->comment('Tên sách');
            \$table->string('isbn')->nullable()->comment('Mã ISBN');
            \$table->text('description')->nullable()->comment('Mô tả nội dung sách');
            \$table->decimal('price', 12, 2)->comment('Giá bán');
            \$table->integer('quantity')->default(0)->comment('Số lượng tồn kho');
            \$table->string('cover_image')->nullable()->comment('Ảnh bìa chính');
            \$table->integer('published_year')->nullable()->comment('Năm xuất bản');
            \$table->string('level')->nullable()->comment('Trình độ (VD: Cơ bản, Nâng cao)');
            \$table->timestamps();
EOT;
updateMigration($dir, 'books', $books);

// 9. Book Images
$book_images = <<<EOT
            \$table->id();
            \$table->foreignId('book_id')->constrained()->cascadeOnDelete()->comment('ID Sách');
            \$table->string('image')->comment('Đường dẫn ảnh');
            \$table->timestamps();
EOT;
updateMigration($dir, 'book_images', $book_images);

// 10. Wishlists
$wishlists = <<<EOT
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID Người dùng');
            \$table->foreignId('book_id')->constrained()->cascadeOnDelete()->comment('ID Sách');
            \$table->timestamps();
EOT;
updateMigration($dir, 'wishlists', $wishlists);

// 11. Coupons
$coupons = <<<EOT
            \$table->id();
            \$table->string('code')->unique()->comment('Mã giảm giá');
            \$table->decimal('discount', 12, 2)->comment('Số tiền giảm');
            \$table->dateTime('start_date')->nullable()->comment('Ngày bắt đầu');
            \$table->dateTime('end_date')->nullable()->comment('Ngày kết thúc');
            \$table->integer('quantity')->default(0)->comment('Số lượt dùng');
            \$table->timestamps();
EOT;
updateMigration($dir, 'coupons', $coupons);

// 12. Orders
$orders = <<<EOT
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID Khách hàng');
            \$table->foreignId('address_id')->nullable()->constrained()->nullOnDelete()->comment('ID Địa chỉ giao hàng');
            \$table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete()->comment('ID Mã giảm giá (nếu có)');
            
            \$table->string('order_code')->unique()->comment('Mã đơn hàng');
            \$table->decimal('total_price', 12, 2)->comment('Tổng tiền đơn hàng');
            \$table->string('payment_method')->default('COD')->comment('Phương thức thanh toán');
            \$table->string('payment_status')->default('Unpaid')->comment('Trạng thái thanh toán (Unpaid, Paid)');
            \$table->string('status')->default('Pending')->comment('Trạng thái giao hàng (Pending, Confirmed, Shipping, Completed, Cancelled)');
            \$table->text('note')->nullable()->comment('Ghi chú của khách hàng');
            \$table->timestamps();
EOT;
updateMigration($dir, 'orders', $orders);

// 13. Order Items
$order_items = <<<EOT
            \$table->id();
            \$table->foreignId('order_id')->constrained()->cascadeOnDelete()->comment('ID Đơn hàng');
            \$table->foreignId('book_id')->constrained()->cascadeOnDelete()->comment('ID Sách');
            \$table->decimal('price', 12, 2)->comment('Giá mua tại thời điểm đặt');
            \$table->integer('quantity')->comment('Số lượng mua');
            \$table->timestamps();
EOT;
updateMigration($dir, 'order_items', $order_items);

// 14. Reviews
$reviews = <<<EOT
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID Người đánh giá');
            \$table->foreignId('book_id')->constrained()->cascadeOnDelete()->comment('ID Sách');
            \$table->tinyInteger('rating')->comment('Số sao (1-5)');
            \$table->text('comment')->nullable()->comment('Nội dung đánh giá');
            \$table->timestamps();
EOT;
updateMigration($dir, 'reviews', $reviews);

// 15. Chatbot Histories
$chatbot_histories = <<<EOT
            \$table->id();
            \$table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete()->comment('ID Người dùng (nếu có đăng nhập)');
            \$table->text('question')->comment('Câu hỏi của khách');
            \$table->text('answer')->comment('Câu trả lời của AI');
            \$table->timestamps();
EOT;
updateMigration($dir, 'chatbot_histories', $chatbot_histories);

// 16. Chatbot Feedbacks
$chatbot_feedback = <<<EOT
            \$table->id();
            \$table->foreignId('chatbot_history_id')->constrained('chatbot_histories')->cascadeOnDelete()->comment('ID đoạn chat');
            \$table->boolean('is_helpful')->comment('Có hữu ích không?');
            \$table->timestamps();
EOT;
updateMigration($dir, 'chatbot_feedback', $chatbot_feedback);
