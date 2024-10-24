<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductModel;

class CategoriesModel extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'cat_id';

    // 제품과 관계 정의
    public function products()
    {
        return $this->hasMany(ProductModel::class, 'cat_id');
    }
}
