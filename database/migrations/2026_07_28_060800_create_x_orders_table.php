<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ID Khách hàng');
            $table->foreignId('address_id')->nullable()->comment('ID Địa chỉ giao hàng');
            $table->foreignId('coupon_id')->nullable()->comment('ID Mã giảm giá (nếu có)');
            
            $table->string('order_code')->unique()->comment('Mã đơn hàng');
            $table->decimal('total_price', 12, 2)->comment('Tổng tiền đơn hàng');
            $table->string('payment_method')->default('COD')->comment('Phương thức thanh toán');
            $table->string('payment_status')->default('Unpaid')->comment('Trạng thái thanh toán (Unpaid, Paid)');
            $table->string('status')->default('Pending')->comment('Trạng thái giao hàng (Pending, Confirmed, Shipping, Completed, Cancelled)');
            $table->text('note')->nullable()->comment('Ghi chú của khách hàng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
