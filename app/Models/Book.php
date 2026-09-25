<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'category_id', 'author_id', 'publisher_id', 'course_id',
        'title', 'isbn', 'description', 'price', 'quantity',
        'cover_image', 'published_year', 'level'
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function author() { return $this->belongsTo(Author::class); }
    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function course() { return $this->belongsTo(Course::class); }
    
    public function images() { return $this->hasMany(BookImage::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function wishlistedBy() { return $this->hasMany(Wishlist::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    /** URL ảnh bìa: ảnh demo nằm trong public/assets, ảnh tải lên nằm trong storage. */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->cover_image) {
                return asset('assets/client/images/product-item1.jpg');
            }

            return str_starts_with($this->cover_image, 'assets/')
                ? asset($this->cover_image)
                : asset('storage/'.$this->cover_image);
        });
    }
}
