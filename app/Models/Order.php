<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Order extends Model
{
    use HasFactory;
    protected  $table='order';

    protected $fillable = [
        'user_id','campaign_id','order_register','price','discount_price'
    ];
    /*
    protected function data(): Attribute
    {
        return Attribute::make(
            'get',
            function ($value) {
                return json_decode($value, true);
            },
            'set',
            function ($value) {
                return json_encode($value);
            }
        );
    }*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot('product_quantity', 'product_price');
    }



}
