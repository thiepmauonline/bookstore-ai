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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->comment('ID danh mục');
            $table->foreignId('author_id')->nullable()->comment('ID tác giả');
            $table->foreignId('publisher_id')->nullable()->comment('ID nhà xuất bản');
            $table->foreignId('course_id')->nullable()->comment('ID học phần');
            
            $table->string('title')->comment('Tên sách');
            $table->string('isbn')->nullable()->comment('Mã ISBN');
            $table->text('description')->nullable()->comment('Mô tả nội dung sách');
            $table->decimal('price', 12, 2)->comment('Giá bán');
            $table->integer('quantity')->default(0)->comment('Số lượng tồn kho');
            $table->string('cover_image')->nullable()->comment('Ảnh bìa chính');
            $table->integer('published_year')->nullable()->comment('Năm xuất bản');
            $table->string('level')->nullable()->comment('Trình độ (VD: Cơ bản, Nâng cao)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
