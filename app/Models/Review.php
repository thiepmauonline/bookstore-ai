<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'book_id', 'rating', 'comment', 'is_visible'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }

    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }
}
