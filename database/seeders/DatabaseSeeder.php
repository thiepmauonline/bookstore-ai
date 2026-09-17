<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Major;
use App\Models\Course;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        User::create(['name' => 'Admin System', 'email' => 'admin@bookstore.test', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'active', 'phone' => '0901234567']);
        User::create(['name' => 'Nguyễn Văn Sinh Viên', 'email' => 'sv@bookstore.test', 'password' => Hash::make('password'), 'role' => 'user', 'status' => 'active', 'phone' => '0987654321']);
        User::create(['name' => 'Trần Thị Khách Hàng', 'email' => 'kh@bookstore.test', 'password' => Hash::make('password'), 'role' => 'user', 'status' => 'active', 'phone' => '0912345678']);

        // 2. Categories
        $cats = [
            'it' => Category::create(['name' => 'Công nghệ thông tin', 'slug' => 'cong-nghe-thong-tin']),
            'eco' => Category::create(['name' => 'Kinh tế & Quản trị', 'slug' => 'kinh-te-quan-tri']),
            'med' => Category::create(['name' => 'Y khoa & Sức khỏe', 'slug' => 'y-khoa-suc-khoe']),
            'eng' => Category::create(['name' => 'Khoa học & Kỹ thuật', 'slug' => 'khoa-hoc-ky-thuat']),
            'lang' => Category::create(['name' => 'Ngoại ngữ', 'slug' => 'ngoai-ngu']),
            'lit' => Category::create(['name' => 'Văn học & Xã hội', 'slug' => 'van-hoc-xa-hoi']),
        ];

        // 3. Authors
        $authors = [
            'it1' => Author::create(['name' => 'Robert C. Martin', 'biography' => 'Kỹ sư phần mềm nổi tiếng, tác giả cuốn Clean Code.']),
            'it2' => Author::create(['name' => 'Thomas H. Cormen', 'biography' => 'Giáo sư khoa học máy tính tại Dartmouth College.']),
            'eco1' => Author::create(['name' => 'Philip Kotler', 'biography' => 'Cha đẻ của Marketing hiện đại.']),
            'eco2' => Author::create(['name' => 'N. Gregory Mankiw', 'biography' => 'Nhà kinh tế học vĩ mô hàng đầu của Mỹ.']),
            'med1' => Author::create(['name' => 'Frank H. Netter', 'biography' => 'Bác sĩ, họa sĩ giải phẫu y khoa vĩ đại.']),
            'eng1' => Author::create(['name' => 'David Halliday', 'biography' => 'Nhà vật lý học người Mỹ, tác giả cuốn Fundamentals of Physics.']),
            'lang1' => Author::create(['name' => 'Raymond Murphy', 'biography' => 'Tác giả sách ngữ pháp tiếng Anh nổi tiếng thế giới.']),
            'lit1' => Author::create(['name' => 'Nguyễn Nhật Ánh', 'biography' => 'Nhà văn Việt Nam nổi tiếng với các tác phẩm viết cho tuổi thơ.']),
        ];

        // 4. Publishers
        $pubs = [
            'p1' => Publisher::create(['name' => 'NXB Giáo Dục Việt Nam', 'address' => 'Hà Nội, Việt Nam']),
            'p2' => Publisher::create(['name' => 'NXB Đại học Quốc gia Hà Nội', 'address' => 'Hà Nội, Việt Nam']),
            'p3' => Publisher::create(['name' => 'NXB Trẻ', 'address' => 'TP.HCM, Việt Nam']),
            'p4' => Publisher::create(['name' => 'Pearson Education', 'address' => 'London, UK']),
            'p5' => Publisher::create(['name' => 'McGraw-Hill Education', 'address' => 'New York, USA']),
            'p6' => Publisher::create(['name' => 'O\'Reilly Media', 'address' => 'California, USA']),
        ];

        // 5. Majors
        $majors = [
            'it' => Major::create(['name' => 'Công nghệ thông tin']),
            'eco' => Major::create(['name' => 'Quản trị kinh doanh']),
            'med' => Major::create(['name' => 'Y Đa Khoa']),
            'eng' => Major::create(['name' => 'Kỹ thuật Cơ khí']),
            'lang' => Major::create(['name' => 'Ngôn ngữ Anh']),
        ];

        // 6. Courses
        $courses = [
            'it_web' => Course::create(['major_id' => $majors['it']->id, 'name' => 'Lập trình Web']),
            'it_algo' => Course::create(['major_id' => $majors['it']->id, 'name' => 'Cấu trúc dữ liệu và Giải thuật']),
            'it_se' => Course::create(['major_id' => $majors['it']->id, 'name' => 'Công nghệ phần mềm']),
            
            'eco_mkt' => Course::create(['major_id' => $majors['eco']->id, 'name' => 'Marketing căn bản']),
            'eco_macro' => Course::create(['major_id' => $majors['eco']->id, 'name' => 'Kinh tế vĩ mô']),
            'eco_micro' => Course::create(['major_id' => $majors['eco']->id, 'name' => 'Kinh tế vi mô']),
            
            'med_ana' => Course::create(['major_id' => $majors['med']->id, 'name' => 'Giải phẫu học']),
            'med_phy' => Course::create(['major_id' => $majors['med']->id, 'name' => 'Sinh lý học']),
            
            'eng_phy' => Course::create(['major_id' => $majors['eng']->id, 'name' => 'Vật lý đại cương']),
            'eng_mec' => Course::create(['major_id' => $majors['eng']->id, 'name' => 'Cơ học lý thuyết']),
            
            'lang_gra' => Course::create(['major_id' => $majors['lang']->id, 'name' => 'Ngữ pháp tiếng Anh']),
            'lang_toeic' => Course::create(['major_id' => $majors['lang']->id, 'name' => 'Luyện thi TOEIC']),
        ];

        // 7. Books (30 Books)
        $booksData = [
            // IT Books
            [
                'cat' => 'it', 'auth' => 'it1', 'pub' => 'p6', 'course' => 'it_se',
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'desc' => 'Sách kinh điển dành cho lập trình viên, hướng dẫn cách viết mã nguồn sạch, dễ đọc và dễ bảo trì. Phù hợp cho môn Công nghệ phần mềm.',
                'price' => 450000, 'img' => 'assets/client/images/books/demo/book-01.png'
            ],
            [
                'cat' => 'it', 'auth' => 'it2', 'pub' => 'p4', 'course' => 'it_algo',
                'title' => 'Introduction to Algorithms (4th Edition)',
                'desc' => 'Giáo trình cốt lõi về thuật toán cho sinh viên ngành khoa học máy tính. Bao trùm từ cơ bản đến nâng cao về cấu trúc dữ liệu.',
                'price' => 850000, 'img' => 'assets/client/images/books/demo/book-02.png'
            ],
            [
                'cat' => 'it', 'auth' => 'it1', 'pub' => 'p1', 'course' => 'it_web',
                'title' => 'Lập Trình Web Căn Bản Với HTML5 & CSS3',
                'desc' => 'Giáo trình tiếng Việt giúp sinh viên nắm vững kiến thức xây dựng giao diện web từ con số 0.',
                'price' => 150000, 'img' => 'assets/client/images/books/demo/book-03.png'
            ],
            [
                'cat' => 'it', 'auth' => 'it1', 'pub' => 'p2', 'course' => 'it_se',
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'desc' => 'Tuyển tập các mẫu thiết kế phần mềm, kim chỉ nam cho kiến trúc sư phần mềm tương lai.',
                'price' => 520000, 'img' => 'assets/client/images/books/demo/book-04.png'
            ],
            [
                'cat' => 'it', 'auth' => 'it2', 'pub' => 'p6', 'course' => 'it_web',
                'title' => 'Laravel Up & Running (3rd Edition)',
                'desc' => 'Cẩm nang toàn diện về framework Laravel dành cho sinh viên và nhà phát triển web.',
                'price' => 380000, 'img' => 'assets/client/images/books/demo/book-05.png'
            ],

            // Economics Books
            [
                'cat' => 'eco', 'auth' => 'eco1', 'pub' => 'p4', 'course' => 'eco_mkt',
                'title' => 'Marketing Management (15th Edition)',
                'desc' => 'Cuốn sách được mệnh danh là "kinh thánh" của ngành Marketing, trình bày toàn bộ kiến thức quản trị tiếp thị.',
                'price' => 650000, 'img' => 'assets/client/images/books/demo/book-06.png'
            ],
            [
                'cat' => 'eco', 'auth' => 'eco2', 'pub' => 'p5', 'course' => 'eco_macro',
                'title' => 'Principles of Macroeconomics',
                'desc' => 'Giáo trình kinh tế vĩ mô chuẩn mực toàn cầu, được giảng dạy tại hầu hết các trường kinh tế.',
                'price' => 550000, 'img' => 'assets/client/images/books/demo/book-07.png'
            ],
            [
                'cat' => 'eco', 'auth' => 'eco2', 'pub' => 'p2', 'course' => 'eco_micro',
                'title' => 'Giáo Trình Kinh Tế Vi Mô',
                'desc' => 'Nghiên cứu hành vi của người tiêu dùng và doanh nghiệp, phân tích thị trường chi tiết.',
                'price' => 120000, 'img' => 'assets/client/images/books/demo/book-08.png'
            ],
            [
                'cat' => 'eco', 'auth' => 'eco1', 'pub' => 'p1', 'course' => 'eco_mkt',
                'title' => 'Marketing Căn Bản',
                'desc' => 'Giáo trình chuẩn do NXB Giáo dục phát hành, dùng cho sinh viên đại học khối ngành kinh tế tại Việt Nam.',
                'price' => 950000, 'img' => 'assets/client/images/books/demo/book-09.png'
            ],
            [
                'cat' => 'eco', 'auth' => 'eco2', 'pub' => 'p5', 'course' => 'eco_macro',
                'title' => 'Kinh Tế Học Vĩ Mô (Bản Dịch Tiếng Việt)',
                'desc' => 'Bản dịch chính thức cuốn sách kinh tế của Gregory Mankiw, sát với thực tiễn.',
                'price' => 250000, 'img' => 'assets/client/images/books/demo/book-10.png'
            ],

            // Medical Books
            [
                'cat' => 'med', 'auth' => 'med1', 'pub' => 'p4', 'course' => 'med_ana',
                'title' => 'Atlas of Human Anatomy',
                'desc' => 'Cuốn Atlas giải phẫu học chi tiết nhất với các hình vẽ vẽ tay kinh điển của bác sĩ Netter.',
                'price' => 1250000, 'img' => 'assets/client/images/books/demo/book-11.png'
            ],
            [
                'cat' => 'med', 'auth' => 'med1', 'pub' => 'p1', 'course' => 'med_phy',
                'title' => 'Giáo Trình Sinh Lý Học Tập 1',
                'desc' => 'Tài liệu bắt buộc cho sinh viên Y khoa năm 2, trình bày cơ chế hoạt động của các cơ quan.',
                'price' => 180000, 'img' => 'assets/client/images/books/demo/book-12.png'
            ],
            [
                'cat' => 'med', 'auth' => 'med1', 'pub' => 'p1', 'course' => 'med_phy',
                'title' => 'Giáo Trình Sinh Lý Học Tập 2',
                'desc' => 'Tiếp nối tập 1, nghiên cứu sâu hơn về nội tiết và thần kinh.',
                'price' => 190000, 'img' => 'assets/client/images/books/demo/book-13.png'
            ],
            [
                'cat' => 'med', 'auth' => 'med1', 'pub' => 'p2', 'course' => 'med_ana',
                'title' => 'Bài Giảng Giải Phẫu Học (ĐH Y Hà Nội)',
                'desc' => 'Sách giáo khoa chính thức được sử dụng rộng rãi tại các trường Y trên toàn quốc.',
                'price' => 220000, 'img' => 'assets/client/images/books/demo/book-14.png'
            ],
            [
                'cat' => 'med', 'auth' => 'med1', 'pub' => 'p5', 'course' => 'med_phy',
                'title' => 'Guyton and Hall Textbook of Medical Physiology',
                'desc' => 'Giáo trình sinh lý học y khoa kinh điển thế giới, rất cần thiết cho bác sĩ tương lai.',
                'price' => 1550000, 'img' => 'assets/client/images/books/demo/book-15.png'
            ],

            // Engineering Books
            [
                'cat' => 'eng', 'auth' => 'eng1', 'pub' => 'p5', 'course' => 'eng_phy',
                'title' => 'Fundamentals of Physics (Extended)',
                'desc' => 'Tài liệu chuẩn mực về Vật lý đại cương cho sinh viên kỹ thuật toàn cầu.',
                'price' => 780000, 'img' => 'assets/client/images/books/demo/book-16.png'
            ],
            [
                'cat' => 'eng', 'auth' => 'eng1', 'pub' => 'p1', 'course' => 'eng_mec',
                'title' => 'Cơ Học Lý Thuyết',
                'desc' => 'Giáo trình cung cấp các nguyên lý cơ bản của cơ học kỹ thuật ứng dụng.',
                'price' => 110000, 'img' => 'assets/client/images/books/demo/book-17.png'
            ],
            [
                'cat' => 'eng', 'auth' => 'eng1', 'pub' => 'p2', 'course' => 'eng_phy',
                'title' => 'Vật Lý Đại Cương Tập 1: Cơ Nhiệt',
                'desc' => 'Dành cho hệ đại học khối ngành Khoa học Tự nhiên và Kỹ thuật.',
                'price' => 1350000, 'img' => 'assets/client/images/books/demo/book-18.png'
            ],
            [
                'cat' => 'eng', 'auth' => 'eng1', 'pub' => 'p2', 'course' => 'eng_phy',
                'title' => 'Vật Lý Đại Cương Tập 2: Điện Từ',
                'desc' => 'Tiếp nối phần cơ nhiệt, tài liệu chuyên sâu về điện và từ trường.',
                'price' => 140000, 'img' => 'assets/client/images/books/demo/book-19.png'
            ],
            [
                'cat' => 'eng', 'auth' => 'eng1', 'pub' => 'p4', 'course' => 'eng_mec',
                'title' => 'Engineering Mechanics: Statics',
                'desc' => 'Giáo trình tĩnh học cho kỹ sư cơ khí và xây dựng.',
                'price' => 650000, 'img' => 'assets/client/images/books/demo/book-20.png'
            ],

            // Language Books
            [
                'cat' => 'lang', 'auth' => 'lang1', 'pub' => 'p4', 'course' => 'lang_gra',
                'title' => 'English Grammar in Use',
                'desc' => 'Cuốn sách ngữ pháp tiếng Anh tự học số 1 thế giới dành cho người học ở trình độ trung cấp.',
                'price' => 320000, 'img' => 'assets/client/images/books/demo/book-21.png'
            ],
            [
                'cat' => 'lang', 'auth' => 'lang1', 'pub' => 'p3', 'course' => 'lang_toeic',
                'title' => 'ETS TOEIC Test 2024',
                'desc' => 'Bộ đề thi TOEIC chuẩn cấu trúc mới nhất, kèm đáp án và giải thích chi tiết.',
                'price' => 280000, 'img' => 'assets/client/images/books/demo/book-22.png'
            ],
            [
                'cat' => 'lang', 'auth' => 'lang1', 'pub' => 'p2', 'course' => 'lang_gra',
                'title' => 'Giải Thích Ngữ Pháp Tiếng Anh',
                'desc' => 'Tài liệu chi tiết bằng tiếng Việt giúp nắm vững hệ thống ngữ pháp tiếng Anh.',
                'price' => 150000, 'img' => 'assets/client/images/books/demo/book-23.png'
            ],
            [
                'cat' => 'lang', 'auth' => 'lang1', 'pub' => 'p4', 'course' => 'lang_toeic',
                'title' => 'Barron\'s TOEIC Practice Exams',
                'desc' => 'Cung cấp nhiều đề thi thử và chiến lược làm bài thi TOEIC điểm cao.',
                'price' => 350000, 'img' => 'assets/client/images/books/demo/book-24.png'
            ],
            [
                'cat' => 'lang', 'auth' => 'lang1', 'pub' => 'p4', 'course' => 'lang_gra',
                'title' => 'Advanced Grammar in Use',
                'desc' => 'Sách ngữ pháp nâng cao, lý tưởng cho sinh viên chuyên ngữ.',
                'price' => 380000, 'img' => 'assets/client/images/books/demo/book-25.png'
            ],

            // Literature Books
            [
                'cat' => 'lit', 'auth' => 'lit1', 'pub' => 'p3', 'course' => null,
                'title' => 'Mắt Biếc',
                'desc' => 'Tác phẩm văn học kinh điển về tuổi thơ và tình yêu, được chuyển thể thành phim điện ảnh.',
                'price' => 110000, 'img' => 'assets/client/images/books/demo/book-26.png'
            ],
            [
                'cat' => 'lit', 'auth' => 'lit1', 'pub' => 'p3', 'course' => null,
                'title' => 'Tôi Thấy Hoa Vàng Trên Cỏ Xanh',
                'desc' => 'Câu chuyện cảm động về tình anh em và những kỷ niệm ấu thơ.',
                'price' => 100000, 'img' => 'assets/client/images/books/demo/book-27.png'
            ],
            [
                'cat' => 'lit', 'auth' => 'lit1', 'pub' => 'p3', 'course' => null,
                'title' => 'Cho Tôi Xin Một Vé Đi Tuổi Thơ',
                'desc' => 'Một cuốn sách mang lại tiếng cười và những suy ngẫm sâu sắc về cuộc sống.',
                'price' => 95000, 'img' => 'assets/client/images/books/demo/book-28.png'
            ],
            [
                'cat' => 'lit', 'auth' => 'lit1', 'pub' => 'p1', 'course' => null,
                'title' => 'Đại Cương Văn Hóa Việt Nam',
                'desc' => 'Giáo trình tổng quan về lịch sử và văn hóa dân tộc.',
                'price' => 125000, 'img' => 'assets/client/images/books/demo/book-29.png'
            ],
            [
                'cat' => 'lit', 'auth' => 'lit1', 'pub' => 'p2', 'course' => null,
                'title' => 'Xã Hội Học Đại Cương',
                'desc' => 'Tài liệu nhập môn khoa học xã hội học, cấu trúc và thiết chế xã hội.',
                'price' => 145000, 'img' => 'assets/client/images/books/demo/book-30.png'
            ],
        ];

        foreach ($booksData as $idx => $b) {
            Book::create([
                'category_id' => $cats[$b['cat']]->id,
                'author_id' => $authors[$b['auth']]->id,
                'publisher_id' => $pubs[$b['pub']]->id,
                'course_id' => $b['course'] ? $courses[$b['course']]->id : null,
                'title' => $b['title'],
                'isbn' => '978' . rand(1000000000, 9999999999),
                'description' => $b['desc'],
                'price' => $b['price'],
                'quantity' => rand(20, 100),
                'published_year' => rand(2015, 2024),
                'level' => 'Đại học',
                'cover_image' => $b['img']
            ]);
        }
        // 8. Addresses & Orders (Dummy data for Chart)
        $userSv = \App\Models\User::where('email', 'sv@bookstore.test')->first();
        $userKh = \App\Models\User::where('email', 'kh@bookstore.test')->first();

        $addressSv = \App\Models\Address::create([
            'user_id' => $userSv->id,
            'receiver_name' => 'Nguyễn Văn Sinh Viên',
            'phone' => '0987654321',
            'province' => 'Hà Nội', 'district' => 'Cầu Giấy', 'ward' => 'Dịch Vọng',
            'address' => 'Ký túc xá ĐH Quốc Gia',
            'is_default' => true
        ]);

        $addressKh = \App\Models\Address::create([
            'user_id' => $userKh->id,
            'receiver_name' => 'Trần Thị Khách Hàng',
            'phone' => '0912345678',
            'province' => 'TP.HCM', 'district' => 'Quận 1', 'ward' => 'Bến Nghé',
            'address' => '123 Lê Lợi',
            'is_default' => true
        ]);

        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'pending', 'processing', 'cancelled'];
        
        $allBooks = Book::all();

        for ($i = 0; $i < 15; $i++) {
            $user = rand(0, 1) ? $userSv : $userKh;
            $address = $user->id == $userSv->id ? $addressSv : $addressKh;
            
            // Random past 7 days
            $daysAgo = rand(0, 6);
            $createdAt = \Carbon\Carbon::now()->subDays($daysAgo)->setTime(rand(8, 22), rand(0, 59));

            // Random items
            $numItems = rand(1, 4);
            $totalPrice = 0;
            $itemsData = [];

            for ($j = 0; $j < $numItems; $j++) {
                $book = $allBooks->random();
                $qty = rand(1, 3);
                $price = $book->price;
                $totalPrice += $price * $qty;
                
                $itemsData[] = [
                    'book_id' => $book->id,
                    'quantity' => $qty,
                    'price' => $price
                ];
            }

            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'order_code' => 'MDH' . date('Ymd', $createdAt->timestamp) . rand(1000, 9999),
                'total_price' => $totalPrice,
                'payment_method' => rand(0, 1) ? 'COD' : 'VNPAY',
                'payment_status' => rand(0, 1) ? 'Paid' : 'Unpaid',
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($itemsData as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }
        }
    }
}
