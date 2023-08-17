<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $json = File::get(storage_path('app/category.json')); // JSON dosya yolunu belirtin

        $data = json_decode($json, true); // true kullanarak dizi olarak çözümle

        foreach ($data as $item) {
            DB::table('category')->insert([
                'category_title' => $item['category_title'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
