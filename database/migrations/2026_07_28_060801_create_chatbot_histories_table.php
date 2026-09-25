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
        Schema::create('chatbot_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('ID Người dùng (nếu có đăng nhập)');
            $table->string('session_id', 100)->nullable()->index()->comment('Phiên trò chuyện (dùng cho cả khách chưa đăng nhập)');
            $table->text('question')->comment('Câu hỏi của khách');
            $table->text('answer')->comment('Câu trả lời của AI');
            $table->json('book_ids')->nullable()->comment('Danh sách ID sách được gợi ý');
            $table->string('source', 20)->default('ai')->comment('Nguồn trả lời: ai (Gemini) hoặc fallback (tìm kiếm nội bộ)');
            $table->unsignedInteger('response_ms')->nullable()->comment('Thời gian phản hồi (ms)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_histories');
    }
};
