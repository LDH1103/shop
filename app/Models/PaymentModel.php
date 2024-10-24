<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderModel;

class PaymentModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'payment';
    protected $primaryKey = 'pay_id';
    protected $fillable = [
        'ord_id',
        'status',
        'price',
        'merchant_uid',
    ];

    public function order()
    {
        return $this->belongsTo(OrderModel::class);
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case '0':
                return '결제완료';
            case '1':
                return '환불';
        }
    }
}
