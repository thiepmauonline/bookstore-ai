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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ID người dùng');
            $table->string('receiver_name')->comment('Tên người nhận');
            $table->string('phone')->comment('Số điện thoại nhận hàng');
            $table->string('province')->comment('Tỉnh/Thành phố');
            $table->string('district')->comment('Quận/Huyện');
            $table->string('ward')->comment('Phường/Xã');
            $table->text('address')->comment('Địa chỉ chi tiết');
            $table->boolean('is_default')->default(false)->comment('Là địa chỉ mặc định');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
