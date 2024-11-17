<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\CartModel;
use App\Models\AddressModel;
use App\Models\ReviewModel;
class User extends Authenticatable
{
    use HasFactory, SoftDeletes;
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name',
        'email',
        'pw',
        'admin_flg',
        'social',
        'remember_token'
    ];

    // 카트와의 관계 정의
    public function cart()
    {
        return $this->hasMany(CartModel::class, 'user_id');
    }

    // 주소와의 관계 정의
    public function addresses()
    {
        return $this->hasMany(AddressModel::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(ReviewModel::class, 'user_id');
    }
}
