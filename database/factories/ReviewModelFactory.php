<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ReviewModel;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReviewModel>
 */
class ReviewModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ReviewModel::class;

    public function definition(): array
    {
        // 상품 ID를 미리 캐싱
        static $productIds = null;
        static $userIds = null;

        if ($productIds === null) {
            $productIds = ProductModel::pluck('pro_id')->toArray(); // 모든 상품 ID 가져오기
        }
    
        if ($userIds === null) {
            $userIds = User::pluck('user_id')->toArray(); // 모든 유저 ID 가져오기
        }

        // 랜덤 선택
        $randomProductId = $this->faker->randomElement($productIds);
        $randomUserId = $this->faker->randomElement($userIds);

        // 긍정적인 리뷰 내용
        $positiveComments = [
            "정말 만족스러운 제품이에요. 다음에 또 구매할 예정입니다.",
            "배송이 빠르고 포장이 깔끔해서 좋았어요.",
            "기능이 생각보다 훌륭하고, 디자인도 예쁩니다.",
            "가격 대비 품질이 훌륭해요. 강력 추천합니다!",
            "사용법이 간단해서 초보자도 쉽게 사용할 수 있어요."
        ];

        // 부정적인 리뷰 내용
        $negativeComments = [
            "제품이 제대로 작동하지 않았습니다. 교환 요청 중입니다.",
            "생각보다 품질이 떨어져서 실망스러웠습니다.",
            "배송이 늦고, 포장이 다소 부실했습니다.",
            "기능이 설명과 달라서 아쉬웠습니다.",
            "고객센터 응대가 느려서 불편했어요."
        ];

        // 중립적인 리뷰 내용
        $neutralComments = [
            "제품은 괜찮은데, 생각했던 것보다는 평범합니다.",
            "사용하기 나쁘진 않지만, 개선의 여지가 있어 보입니다.",
            "배송이 보통이었고, 품질도 나쁘지 않았습니다.",
            "가격 대비 적당한 성능입니다. 큰 기대는 하지 마세요.",
            "전체적으로 만족스럽지만, 특별히 돋보이는 점은 없었습니다."
        ];

        // 별점 생성 (0.5 ~ 5.0)
        $rating = $this->faker->numberBetween(1, 10) * 0.5;

        // 별점에 따라 리뷰 내용 선택
        $comment = $rating > 3
            ? $this->faker->randomElement($positiveComments) // 긍정적 리뷰
            : ($rating < 3
                ? $this->faker->randomElement($negativeComments) // 부정적 리뷰
                : $this->faker->randomElement($neutralComments)); // 중립적 리뷰

        return [
            'user_id'   => $randomUserId, // 유저 ID
            'pro_id'    => $randomProductId, // 상품 ID
            'ord_id'    => 24, // 주문 ID
            'rating'    => $rating, // 별점
            'comment'   => $comment, // 선택된 리뷰 내용
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
