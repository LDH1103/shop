<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ReviewModel;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 상품정보 팩토리
        // ProductModel::factory()->count(5000)->create();

        // 리뷰 팩토리
        for ($i = 0; $i < 100; $i++) {
            ReviewModel::factory()->count(5000)->create();
        }

        // $this->call([
            // 카테고리 시더
            // CategorySeeder::class,
        // ]);
    }
}
