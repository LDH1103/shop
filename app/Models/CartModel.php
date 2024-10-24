<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ProductModel;

class CartModel extends Model
{
    use HasFactory;
    protected $table = 'cart';
    // protected $primaryKey = ['user_id', 'pro_id'];
    protected $primaryKey = 'cart_id';
    protected $fillable = [
        'user_id',
        'pro_id',
        'quantity'
    ];

    // 유저와 관계 정의
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 상품과 관계 정의
    public function product() {
        return $this->belongsTo(ProductModel::class, 'pro_id');
    }

    // 복합키를 처리하기 위한 메서드 오버라이드
    // protected function getKeyForSaveQuery()
    // {
    //     $query = parent::newModelQuery();
    //     foreach ($this->primaryKey as $key) {
    //         $query->where($key, '=', $this->getAttribute($key));
    //     }
    //     return $query;
    // }
}
