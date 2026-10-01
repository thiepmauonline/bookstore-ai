<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep historical addresses intact; new addresses only require province and ward.
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('district')->nullable()->change();
        });
    }

    public function down(): void
    {
        // New addresses have no district, so rollback must still accept omitted values.
        DB::table('addresses')->whereNull('district')->update(['district' => '']);

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('district')->nullable(false)->default('')->change();
        });
    }
};
