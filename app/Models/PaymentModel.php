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
        return $this->belongsTo(OrderModel::class, 'ord_id', 'ord_id');
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 'P':
                return '결제완료';
            case 'R':
                return '환불';
        }
    }
}
