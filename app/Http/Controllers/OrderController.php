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
use function Illuminate\Events\queueable;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $orderItems = $request->input('order_items');
        $username = $request->input('username');

        $validator = Validator::make([
            'order_items' => $orderItems,
            'username' => $username
        ], [
            'order_items' => 'required|array',
            'username' => 'required|exists:users,username',
            'order_items.*.id' => 'required|exists:product,id',
            'order_items.*.stock_quantity' => 'required|integer|min:1',
        ], [
            'order_items.required' => 'Sipariş ürünleri gereklidir.',
            'order_items.array' => 'Sipariş ürünleri dizi biçiminde olmalıdır.',
            'username.required' => 'Kullanıcı adı gereklidir.',
            'username.exists' => 'Belirtilen kullanıcı adı mevcut değil.',
            'order_items.*.id.required' => 'Ürün kimliği gereklidir.',
            'order_items.*.id.exists' => 'Belirtilen ürün mevcut değil.',
            'order_items.*.stock_quantity.required' => 'Stok miktarı gereklidir.',
            'order_items.*.stock_quantity.integer' => 'Stok miktarı bir tamsayı olmalıdır.',
            'order_items.*.stock_quantity.min' => 'Stok miktarı en az 1 olmalıdır.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        return $this->calculateOrder($orderItems,$username);
    }

    private function calculateOrder($orderItems, $username)
    {
        $price = 0;
        $allProductsAvailable = true;
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Kullanıcı bulunamadı.'], 404);
        }

        foreach ($orderItems as $item) {
            $product = Product::find($item['id']);
            $requestedQuantity = $item['stock_quantity'];

            if (!$product || $product->stock_quantity < $requestedQuantity) {
                $allProductsAvailable = false;
                break;
            }

            $price += $requestedQuantity * $product->list_price;
        }


        $discount=$this->applyBestCampaign($orderItems,$price);
        $discount=$price-$discount;
        if ($discount <= 50) {
            $discount += 10;
        }
        if (!$allProductsAvailable) {
            return response()->json(['message' => 'Bazı ürünler stokta yok veya yetersiz. Sipariş alınamadı.'], 400);
        }

        return $this->productOrder($user, $orderItems,$price,$discount);
    }

    private function productOrder($user, $orderItems, $price,$discount)
    {
        foreach ($orderItems as $item) {
            $product = Product::find($item['id']);
            $requestedQuantity = $item['stock_quantity'];
            $product->stock_quantity -= $requestedQuantity;
            $product->save();
        }
        $order=$user->getOrder()->create([
            'price'=>$price,
            'discount_price' => $discount
        ]);
        foreach ($orderItems as $item) {
            $product = Product::find($item['id']);
            $order->products()->attach($item['id'], [
                'product_quantity' => $item['stock_quantity'],
                'product_price' => $product->list_price,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['message' => $user->username . "'ın siparişi alındı tutar: " . $price], 200);
    }
    public function applyBestCampaign($orderItems,$total_price){
        $campaigns = Campaign::all();
        $discountAmount = [];
        $discount=0;
        foreach ($campaigns as $campaign) {
            $min_condition= $campaign->min_condition;
            switch($campaign->discount_type){
                case 1: // Sabahattin Ali'nin Roman kitaplarında 2 üründen 1 tanesi bedava
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
                        foreach ($orderItems as $item) {
                            $author = Author::find($item['id']);
                            $product = Product::find($item['id']);
                            if ($author_local == $author->author_type) {
                                $discount = $campaign->gift_condition * $product->list_price / 100;
                                $total_discount += $discount * $item['stock_quantity'];
                            }
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
