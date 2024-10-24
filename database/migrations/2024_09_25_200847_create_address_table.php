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
        Schema::create('address', function (Blueprint $table) {
            $table->id('add_id');
            $table->bigInteger('user_id');
            $table->string('postcode', 5); // 우편번호
            $table->string('address', 100); // 기본 주소
            $table->string('detailAddress', 50)->nullable(); // 상세주소
            $table->string('extraAddress', 50)->nullable(); // 참고항목
            $table->string('recipient', 30); // 수령인
            $table->string('phone', 13); // 전화번호
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
