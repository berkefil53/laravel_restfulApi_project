<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category; // Ekledik
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function productAdd(Request $request)
    {
        $rules = [
            'product_id' => 'required',
            'title' => 'required',
            'category_id' => 'required',
            'category_title' => 'required',
            'author' => 'required',
            'list_price' => 'required',
            'stock_quantity' => 'required',
        ];

        $messages = [
            'product_id.required' => '1',
            'title.required' => '1',
            'category_id.required' => '1',
            'category_title.required' => '1',
            'author.required' => '1',
            'list_price.required' => '1',
            'stock_quantity.required' => '1',
        ];

        $validated = $this->validate($request, $rules, $messages);

        $categoryExists = Category::where('category_id', $validated['category_id'])->exists();

        if (!$categoryExists) {
            return response()->json(['message' => 'Geçersiz Kategori.'], 400);
        }

        $islem = Product::create($validated);

        if (!$islem) {
            return response()->json(['message' => 'Ürün Ekleme Başarısız.'], 500);
        }

        return response()->json(['message' => 'Ürün Ekleme Başarılı.'], 200);
    }
}
