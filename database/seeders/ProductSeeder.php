<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(storage_path('app/product.json')); // JSON dosya yolunu belirtin

        $data = json_decode($json);

        foreach ($data as $item) {
            DB::table('product')->insert([
                'title' => $item->title,
                'category_id' => $item->category_id,
                'category_title' => $item->category_title,
                'author' => $item->author,
                'list_price' => $item->list_price,
                'stock_quantity' => $item->stock_quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
