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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã giảm giá');
            $table->decimal('discount', 12, 2)->comment('Số tiền giảm');
            $table->dateTime('start_date')->nullable()->comment('Ngày bắt đầu');
            $table->dateTime('end_date')->nullable()->comment('Ngày kết thúc');
            $table->integer('quantity')->default(0)->comment('Số lượt dùng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
