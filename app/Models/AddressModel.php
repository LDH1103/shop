<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class AddressModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'address';
    protected $primaryKey = 'add_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'guest_uuid',
        'postcode',
        'address',
        'detailAddress',
        'extraAddress',
        'recipient',
        'phone',
        'default',
    ];

    // 유저와 관계 정의
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(OrderModel::class, 'add_id', 'add_id');
    }
}
