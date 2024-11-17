<?php

namespace App\Models;

use App\Models\User;
use App\Models\ReviewModel;
use App\Models\PaymentModel;
use App\Models\OrderItemsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order';
    protected $primaryKey = 'ord_id';
    protected $fillable = [
        'user_id',
        'guest_uuid',
        'add_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItemsModel::class, 'ord_id', 'ord_id');
    }

    public function payment()
    {
        return $this->hasOne(PaymentModel::class, 'ord_id', 'ord_id');
    }

    public function address()
    {
        return $this->belongsTo(AddressModel::class, 'add_id', 'add_id')->withTrashed();
    }

    public function review()
    {
        return $this->hasMany(ReviewModel::class, 'ord_id', 'ord_id');
    }
}
