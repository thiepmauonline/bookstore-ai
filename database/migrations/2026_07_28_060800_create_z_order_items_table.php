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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->comment('ID Đơn hàng');
            $table->foreignId('book_id')->comment('ID Sách');
            $table->decimal('price', 12, 2)->comment('Giá mua tại thời điểm đặt');
            $table->integer('quantity')->comment('Số lượng mua');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
