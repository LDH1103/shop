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
        Schema::table('cart', function (Blueprint $table) {
            // 기존 복합 키를 삭제
            $table->dropPrimary(['user_id', 'pro_id']);

            // 새로운 auto-increment 기본 키 추가
            $table->bigIncrements('cart_id')->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
