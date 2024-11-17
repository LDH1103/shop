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
        Schema::table('address', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change(); // user_id를 nullable로 변경
            $table->uuid('guest_uuid')->nullable()->after('user_id'); // 비회원 고유 식별자
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('address', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change(); // 롤백 시 nullable 제거
            $table->uuid('guest_uuid')->nullable()->after('user_id'); // 비회원 고유 식별자
        });
    }
};
