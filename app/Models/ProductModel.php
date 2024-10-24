<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\CategoriesModel;

class ProductModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'product';
    protected $primaryKey = 'pro_id';
    protected $fillable = [
        'user_id',
        'cat_id',
        'name',
        'price',
        'description',
        'img',
        'status'
    ];

    // 카테고리와 관계 정의
    public function category()
    {
        return $this->belongsTo(CategoriesModel::class, 'cat_id');
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case '0':
                return '판매중';
            case '1':
                return '품절';
            case '2':
                return '숨김';
            default:
                return '알 수 없음';
        }
    }
}
