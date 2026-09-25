<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('ID người dùng (nếu đã đăng nhập)');
            $table->string('name')->comment('Họ tên người gửi');
            $table->string('email')->comment('Email người gửi');
            $table->text('message')->comment('Nội dung liên hệ');
            $table->boolean('is_handled')->default(false)->index()->comment('Đã xử lý chưa');
            $table->timestamp('handled_at')->nullable()->comment('Thời điểm xử lý');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
