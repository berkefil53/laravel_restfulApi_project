<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Author;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CampaignController extends Controller
{
    public function applyBestCampaign($orderItems,$total_price){
        $campaigns = Campaign::all();
        $discountAmount = [];
        $discount=0;
        foreach ($campaigns as $campaign) {
            $min_condition= $campaign->min_condition;
            switch($campaign->discount_type){
                case 1: //hediye ürün kampanyası
                    $counter = 0;
                    $cheapest_book = null;
                    foreach ($orderItems as $item)
                    {
                        $author_id=$campaign->conditions["author_id"];
                        $category_id=$campaign->conditions["category_id"];
                        $product = Product::find($item['id']);
                        if($product->author_id == $author_id && $product->category_id == $category_id) {
                            $counter += $item['stock_quantity'];
                            if ($cheapest_book == null || $product->list_price < $cheapest_book)//en düşük fiyatlı kitap
                                $cheapest_book = $product->list_price;
                        }
                    }
                    if($counter >= $campaign->min_condition){
                        $discountAmount[] = $cheapest_book*$campaign->gift_condition;
                    }
                    break;
                case 2: //
                    if($min_condition>0)
                    {
                        $total_discount = 0;
                        if ($total_price>=$min_condition)
                        {
                            $total_discount=$campaign->gift_condition*$total_price/100;

                        }
                        $discountAmount[]=$total_discount;
                    }
                    else {
                        $author_local=$campaign->conditions["author_local"];
                        $total_discount=0;
                        $i=0;
                        foreach ($orderItems as $item) {
                            $product = Product::find($item['id']);
                            $author[] = $product->author->author_type;
                            if ($author_local ==$author[$i]) {
                                $discount =$campaign->gift_condition*$product->list_price/100;
                                $total_discount += $discount * $item['stock_quantity'];
                            }
                            $i++;
                        }
                        $discountAmount[] = $total_discount;
                    }
                    break;
                default:
                    break;
            }
        }
        return max($discountAmount);
    }
}
