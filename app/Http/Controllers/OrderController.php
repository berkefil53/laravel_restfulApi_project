<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
public function createOrder(Request $request)
{
    $orderItems = $request->input('order_items'); // Sipariş ürünlerini al
    $username = $request->input('username'); // Kullanıcının username bilgisini al
    $price = 0;
    $allProductsAvailable = true;
    $user = User::where('username', $username)->first();
    if (!$user) {
        return response()->json(['message' => 'Kullanıcı bulunamadı.'], 404);
    }
    foreach ($orderItems as $item) {
        $product = Product::find($item['id']); // Ürünü veritabanından al
        $requestedQuantity = $item['stock_quantity']; // Kullanıcının talep ettiği miktar

        if (!$product || $product->stock_quantity < $requestedQuantity) {
            $allProductsAvailable = false;
            break;
        }

        $price += $requestedQuantity * $product->list_price;
    }
    if ($price <= 50) {
        $price += 10;
    }
    if (!$allProductsAvailable) {
        return response()->json(['message' => 'Bazı ürünler stokta yok veya yetersiz. Sipariş alınamadı.'], 400);
    }
    $data['order_register'] = [];

    foreach ($orderItems as $item) {
        $product = Product::find($item['id']);
        $requestedQuantity = $item['stock_quantity'];
        $productData = [
            'product_id' => $item['id'],
            'quantity' => $item['stock_quantity']
        ];
        $data['order_register'][] = $productData;
        $product->stock_quantity -= $requestedQuantity;
        $product->save();
    }
    $data['order_register'] = json_encode($data['order_register']);
    // Order modelini kullanarak yeni siparişi oluşturma işlemi
    $data['user_id'] = $user->id;
    $data['price'] = $price;
    Order::create($data);
    return response()->json(['message' => $user->username . "'ın siparişi alındı tutar: " . $price], 200);
}
}
