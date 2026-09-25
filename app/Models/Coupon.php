<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount', 'start_date', 'end_date', 'quantity'];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function orders() { return $this->hasMany(Order::class); }

    /** Lý do mã không dùng được, hoặc null nếu mã hợp lệ. */
    public function unavailableReason(): ?string
    {
        if (! now()->between($this->start_date, $this->end_date)) {
            return 'Mã giảm giá đã hết hạn hoặc chưa có hiệu lực.';
        }

        if ($this->quantity <= 0) {
            return 'Mã giảm giá đã hết lượt sử dụng.';
        }

        return null;
    }
}
