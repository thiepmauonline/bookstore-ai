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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ID Người đánh giá');
            $table->foreignId('book_id')->comment('ID Sách');
            $table->tinyInteger('rating')->comment('Số sao (1-5)');
            $table->text('comment')->nullable()->comment('Nội dung đánh giá');
            $table->boolean('is_visible')->default(true)->comment('Hiển thị công khai (admin có thể ẩn)');
            $table->timestamps();

            $table->unique(['user_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
