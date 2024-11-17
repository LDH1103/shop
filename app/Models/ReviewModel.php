<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\ProductModel;

class ReviewModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'review';
    protected $primaryKey = 'rev_id';

    protected $fillable = [
        'user_id',
        'pro_id',
        'ord_id',
        'rating',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'pro_id');
    }

    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'ord_id', 'ord_id')->withDefault();
    }
}
