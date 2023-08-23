<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CampaignFunctionsController extends Controller
{
    public function CampaignGiftDiscount($orderItems,$campaign)
    {
        $counter = 0;
        $cheapest_book = null;
        $discountAmount=[];
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
            $discountAmount[]=
                [
                    'total'=> $cheapest_book * $campaign->gift_condition,
                    'index'=>$campaign->campaign_name,
                    'id'=>$campaign->id
                ];
        }
        return $discountAmount;
    }
    public function CampaignPercentDiscount($min_condition,$total_price,$campaign,$orderItems)
    {
        $discountAmount=[];
        if($min_condition>0)
        {
            $total_discount = 0;
            if ($total_price>=$min_condition)
            {
                $total_discount=$campaign->gift_condition*$total_price/100;

            }
            $discountAmount[]=
                [
                    'total'=> $total_discount,
                    'index'=>$campaign->campaign_name,
                    'id'=>$campaign->id
                ];
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
            $discountAmount[]=
                [
                    'total'=> $total_discount,
                    'index'=>$campaign->campaign_name,
                    'id'=>$campaign->id
                ];
        }
        return $discountAmount;
    }
}
