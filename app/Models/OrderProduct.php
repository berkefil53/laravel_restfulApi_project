<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected  $table='order_product';

    protected $fillable = [
        'order_id','product_id','product_quantity','product_price'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
