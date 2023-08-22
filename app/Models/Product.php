<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected  $table='product';
    protected $fillable = [
        'title','category_id','category_title','author_id','list_price','stock_quantity',
    ];
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product');

    }
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

}
