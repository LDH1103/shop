<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderModel;
use App\Models\ProductModel;

class OrderItemsModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'order';
    protected $primaryKey = 'ord_item_id';
    protected $fillable = [
        'ord_id',
        'pro_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(OrderModel::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class);
    }
}
