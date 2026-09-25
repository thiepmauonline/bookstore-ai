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
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Tổng tiền hàng trước giảm giá');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('Số tiền được giảm');
            $table->decimal('total_price', 12, 2)->comment('Tổng tiền phải thanh toán');
            $table->string('payment_method', 20)->default('cod')->comment('Phương thức thanh toán (cod)');
            $table->string('payment_status', 20)->default('unpaid')->comment('Trạng thái thanh toán (unpaid, paid)');
            $table->string('status', 20)->default('pending')->index()->comment('Trạng thái đơn (pending, confirmed, shipping, completed, cancelled)');
            $table->text('note')->nullable()->comment('Ghi chú của khách hàng');
            $table->string('cancel_reason')->nullable()->comment('Lý do hủy đơn');
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
