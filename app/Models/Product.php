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

}
