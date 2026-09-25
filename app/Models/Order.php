<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'coupon_id', 'order_code',
        'subtotal', 'discount_amount', 'total_price',
        'payment_method', 'payment_status', 'status', 'note', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function address() { return $this->belongsTo(Address::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}
