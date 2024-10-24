<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AddressModel extends Model
{
    use HasFactory;

    protected $table = 'address';
    protected $primaryKey = 'add_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'postcode',
        'address',
        'detailAddress',
        'extraAddress',
        'recipient',
        'phone',
        'default'
    ];

    // 유저와 관계 정의
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
