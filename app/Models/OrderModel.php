<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\OrderItemModel;
use App\Models\PaymentModel;

class OrderModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'order';
    protected $primaryKey = 'ord_id';
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItemModel::class);
    }

    public function payment()
    {
        return $this->hasOne(PaymentModel::class);
    }
}
