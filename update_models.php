<?php
$dir = __DIR__ . '/app/Models/';

$models = [
    'User' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected \$fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'status', 'role'
    ];

    protected \$hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses() { return \$this->hasMany(Address::class); }
    public function orders() { return \$this->hasMany(Order::class); }
    public function wishlists() { return \$this->hasMany(Wishlist::class); }
    public function reviews() { return \$this->hasMany(Review::class); }
    public function chatbotHistories() { return \$this->hasMany(ChatbotHistory::class); }
}
EOT,

    'Address' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected \$fillable = ['user_id', 'receiver_name', 'phone', 'province', 'district', 'ward', 'address', 'is_default'];

    public function user() { return \$this->belongsTo(User::class); }
}
EOT,

    'Category' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected \$fillable = ['name', 'slug'];

    public function books() { return \$this->hasMany(Book::class); }
}
EOT,

    'Author' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected \$fillable = ['name', 'biography'];

    public function books() { return \$this->hasMany(Book::class); }
}
EOT,

    'Publisher' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected \$fillable = ['name', 'address'];

    public function books() { return \$this->hasMany(Book::class); }
}
EOT,

    'Major' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected \$fillable = ['name'];

    public function courses() { return \$this->hasMany(Course::class); }
}
EOT,

    'Course' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected \$fillable = ['major_id', 'name'];

    public function major() { return \$this->belongsTo(Major::class); }
    public function books() { return \$this->hasMany(Book::class); }
}
EOT,

    'Book' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected \$fillable = [
        'category_id', 'author_id', 'publisher_id', 'course_id',
        'title', 'isbn', 'description', 'price', 'quantity',
        'cover_image', 'published_year', 'level'
    ];

    public function category() { return \$this->belongsTo(Category::class); }
    public function author() { return \$this->belongsTo(Author::class); }
    public function publisher() { return \$this->belongsTo(Publisher::class); }
    public function course() { return \$this->belongsTo(Course::class); }
    
    public function images() { return \$this->hasMany(BookImage::class); }
    public function reviews() { return \$this->hasMany(Review::class); }
    public function wishlistedBy() { return \$this->hasMany(Wishlist::class); }
    public function orderItems() { return \$this->hasMany(OrderItem::class); }
}
EOT,

    'BookImage' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookImage extends Model
{
    protected \$fillable = ['book_id', 'image'];

    public function book() { return \$this->belongsTo(Book::class); }
}
EOT,

    'Wishlist' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected \$fillable = ['user_id', 'book_id'];

    public function user() { return \$this->belongsTo(User::class); }
    public function book() { return \$this->belongsTo(Book::class); }
}
EOT,

    'Coupon' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected \$fillable = ['code', 'discount', 'start_date', 'end_date', 'quantity'];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function orders() { return \$this->hasMany(Order::class); }
}
EOT,

    'Order' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected \$fillable = [
        'user_id', 'address_id', 'coupon_id', 'order_code',
        'total_price', 'payment_method', 'payment_status', 'status', 'note'
    ];

    public function user() { return \$this->belongsTo(User::class); }
    public function address() { return \$this->belongsTo(Address::class); }
    public function coupon() { return \$this->belongsTo(Coupon::class); }
    public function items() { return \$this->hasMany(OrderItem::class); }
}
EOT,

    'OrderItem' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected \$fillable = ['order_id', 'book_id', 'price', 'quantity'];

    public function order() { return \$this->belongsTo(Order::class); }
    public function book() { return \$this->belongsTo(Book::class); }
}
EOT,

    'Review' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected \$fillable = ['user_id', 'book_id', 'rating', 'comment'];

    public function user() { return \$this->belongsTo(User::class); }
    public function book() { return \$this->belongsTo(Book::class); }
}
EOT,

    'ChatbotHistory' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotHistory extends Model
{
    protected \$fillable = ['user_id', 'question', 'answer'];

    public function user() { return \$this->belongsTo(User::class); }
    public function feedbacks() { return \$this->hasMany(ChatbotFeedback::class); }
}
EOT,

    'ChatbotFeedback' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFeedback extends Model
{
    protected \$table = 'chatbot_feedback';
    protected \$fillable = ['chatbot_history_id', 'is_helpful'];

    public function history() { return \$this->belongsTo(ChatbotHistory::class, 'chatbot_history_id'); }
}
EOT,
];

foreach ($models as $name => $content) {
    file_put_contents($dir . $name . '.php', str_replace('\\$', '$', $content));
    echo "Updated Model: $name\n";
}
?>
