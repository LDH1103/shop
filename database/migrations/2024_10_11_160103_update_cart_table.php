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
        // 장바구니 테이블에 복합 기본 키 추가
        Schema::table('cart', function (Blueprint $table) {
            // 복합 기본 키 추가
            $table->primary(['user_id', 'pro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
