<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductModel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductModel>
 */
class ProductModelFactory extends Factory
{
    // 각 카테고리의 상품 번호를 추적하기 위한 static 배열
    private static $categoryProductCount = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   

        // 1~7 사이의 랜덤 카테고리 PK 선택
        $categoryId = $this->faker->numberBetween(1, 7);

        // 각 카테고리에 맞는 상품 이름과 이미지 주소를 정의
        $products = [
            // 의류, 잡화
            1 => [
                [
                    'name'  => '가방', 
                    'img'   => 'fac_img/bag.jpg',
                    'description' => json_encode([
                        'fac_img/bag_d1.jpg',
                        ])
                ],
                [
                    'name'  => '바지', 
                    'img'   => 'fac_img/pants.jpg',
                    'description' => json_encode([
                        'fac_img/pants_d1.jpg',
                        'fac_img/pants_d2.jpg'
                        ])
                ],
                [
                    'name'  => '티셔츠', 
                    'img'   => 'fac_img/shirts.jpg',
                    'description' => json_encode([
                        'fac_img/shirts_d1.jpg',
                        ])
                ],
            ],
            // 뷰티
            2 => [
                [
                    'name'  => '틴트', 
                    'img'   => 'fac_img/tint.jpg',
                    'description' => json_encode([
                        'fac_img/tint_d1.jpg',
                        'fac_img/tint_d2.jpg',
                        ])
                ],
                [
                    'name'  => '수분 크림', 
                    'img'   => 'fac_img/cream.jpg',
                    'description' => json_encode([
                        'fac_img/cream_d1.jpg',
                        ])
                ],
                [
                    'name'  => '향수', 
                    'img'   => 'fac_img/perfume.jpg',
                    'description' => json_encode([
                        'fac_img/perfume_d1.jpg',
                        ])
                ],
            ],
            // 생활용품
            3 => [
                [
                    'name'  => '휴지', 
                    'img'   => 'fac_img/tissue.jpg',
                    'description' => json_encode([
                        'fac_img/tissue_d1.jpg',
                        ])
                ],
                [
                    'name'  => '물티슈', 
                    'img'   => 'fac_img/wet_tissue.jpg',
                    'description' => json_encode([
                        'fac_img/wet_tissue_d1.jpg',
                        'fac_img/wet_tissue_d2.jpg',
                        ])
                ],
                [
                    'name'  => '멀티탭', 
                    'img'   => 'fac_img/multitab.jpg', 
                    'description' => json_encode([
                        'fac_img/multitab_d1.jpg',
                        'fac_img/multitab_d2.jpg',
                        ])
                ],
            ],
            // 식품
            4 => [
                [
                    'name'  => '콜라', 
                    'img'   => 'fac_img/coke_zero.jpg',
                    'description' => json_encode([
                        'fac_img/coke_zero_d1.jpg',
                        ])
                ],
                [
                    'name'  => '생수', 
                    'img'   => 'fac_img/water.jpg',
                    'description' => json_encode([
                        'fac_img/water_d1.jpg',
                        ])
                ],
                [
                    'name'  => '치킨 너겟', 
                    'img'   => 'fac_img/nuggets.jpg',
                    'description' => json_encode([
                        'fac_img/nuggets_d1.jpg',
                        'fac_img/nuggets_d2.jpg',
                        ])
                ],
            ],
            // 건강식품
            5 => [
                [
                    'name'  => '비타민', 
                    'img'   => 'fac_img/vitamin.jpg',
                    'description' => json_encode([
                        'fac_img/vitamin_d1.jpg',
                        ])
                ],
                [
                    'name'  => '유산균', 
                    'img'   => 'fac_img/Lacto.jpg',
                    'description' => json_encode([
                        'fac_img/Lacto_d1.jpg',
                        ])
                ],
                [
                    'name'  => '루테인', 
                    'img'   => 'fac_img/lutein.jpg',
                    'description' => json_encode([
                        'fac_img/lutein_d1.jpg',
                        ])
                ],
            ],
            // 디지털
            6 => [
                [
                    'name'  => '노트북', 
                    'img'   => 'fac_img/notebook.jpg',
                    'description' => json_encode([
                        'fac_img/notebook_d1.jpg',
                        ])
                ],
                [
                    'name'  => '모니터', 
                    'img'   => 'fac_img/monitor.jpg',
                    'description' => json_encode([
                        'fac_img/monitor_d1.jpg',
                        ])
                ],
                [
                    'name'  => '태블릿', 
                    'img'   => 'fac_img/tablet.jpg',
                    'description' => json_encode([
                        'fac_img/tablet_d1.jpg',
                        ])
                ],
            ],
            // 반려동물
            7 => [
                [
                    'name'  => '장난감', 
                    'img'   => 'fac_img/dog_toy.jpg',
                    'description' => json_encode([
                        'fac_img/dog_toy_d1.jpg',
                        ])
                ],
                [
                    'name'  => '급식기', 
                    'img'   => 'fac_img/dog_bowl.jpg',
                    'description' => json_encode([
                        'fac_img/dog_bowl_d1.jpg',
                        ])
                ],
                [
                    'name'  => '사료', 
                    'img'   => 'fac_img/dog_food.jpg',
                    'description' => json_encode([
                        'fac_img/dog_food_d1.jpg',
                        ])
                ],
            ],
        ];

        // 랜덤 상품 선택
        $product = $this->faker->randomElement($products[$categoryId]);

        // 해당 카테고리의 상품 번호 증가(팩토리한 상품 구분용)
        if (!isset(self::$categoryProductCount[$categoryId])) {
            self::$categoryProductCount[$categoryId] = 1;
        } else {
            self::$categoryProductCount[$categoryId]++;
        }

        // 상품 이름 뒤에 번호 붙이기
        $productNameWithNumber = $product['name'] . '_' . self::$categoryProductCount[$categoryId];

        return [
            'user_id' => 3,
            'cat_id' => $categoryId,
            'name' => $productNameWithNumber,
            // 'description' => $product['name'] . ' 입니다.',
            'description' => $product['description'],
            'price' => rand(10, 1000) . '00',
            'img' => $product['img'],
        ];
    }
}
