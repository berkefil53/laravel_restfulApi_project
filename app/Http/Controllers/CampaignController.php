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
        foreach ($campaigns as $campaign) {
            $min_condition= $campaign->min_condition;
            switch($campaign->discount_type){
                case 1:
                    $discountAmount[]=(new CampaignFunctionsController)->CampaignGiftDiscount($orderItems,$campaign);
                    break;
                case 2:
                    $discountAmount[]=(new CampaignFunctionsController)->CampaignPercentDiscount($min_condition,$total_price,$campaign,$orderItems);
                    break;
                default:
                    break;
            }
        }
        return max($discountAmount);
    }
}
