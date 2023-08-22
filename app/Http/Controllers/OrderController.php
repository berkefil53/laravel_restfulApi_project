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
use App\Http\Controllers\CampaignController;
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

            // Ürün fiyatı ve miktarını çarpıp toplama ekle
            $price += $product->list_price * $requestedQuantity;
        }
        if (!$allProductsAvailable) {
            return response()->json(['message' => 'Bazı ürünler stokta yok veya yetersiz. Sipariş alınamadı.'], 400);
        }

        $discount=(new CampaignController)->applyBestCampaign($orderItems,$price);
        $discount=$price-$discount;
        if ($discount <= 50) {
            $discount += 10;
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
        $orderNumber=$order->id;
        foreach ($orderItems as $item) {
            $product = Product::find($item['id']);
            $order->products()->attach($item['id'], [
                'product_quantity' => $item['stock_quantity'],
                'product_price' => $product->list_price,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $this->orderDetail($orderNumber);
    }
    public function orderDetail($orderNumber)
    {
        $order = Order::where('id', $orderNumber)->first();

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }
        $username = $order->user->username;
        $orderProducts = $order->products;

        $productDetails = []; // Bu kısmı gereksiz olduğunu varsayarak eklemeyin eğer eklemişseniz tekrar eklemenize gerek yok.

        foreach ($orderProducts as $orderProduct) {
            $productDetails[] = [
                'product_id' => $orderProduct->id,
                'product_title' => $orderProduct->title,
                'product_price' => $orderProduct->pivot->product_price,
                'product_quantity' => $orderProduct->pivot->product_quantity,
                'category_title' => $orderProduct->category_title,
            ];
        }
        $sonuc[]=$username." Kullanıcısı :";
        for ($i = 0; $i < count($productDetails); $i++) {
            $sonuc[] = $productDetails[$i]["product_title"] . " adlı ".$productDetails[$i]["category_title"]." kitabı ürününden ". $productDetails[$i]["product_quantity"] . " adet satın aldı."
            ." liste fiyatı : ".$productDetails[$i]["product_price"];
        }
        $sonuc[]=str_repeat("-", 100);
        return response()->json(['Sipariş Detayı' => $sonuc]);

    }
}
