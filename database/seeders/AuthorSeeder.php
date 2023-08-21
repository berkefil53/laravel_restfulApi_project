<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(storage_path('app/author.json')); // JSON dosya yolunu belirtin

        $data = json_decode($json, true); // true kullanarak dizi olarak çözümle

        foreach ($data as $item) {
            DB::table('author')->insert([
                'author_name' => $item['author_name'],
                'author_type' => $item['author_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
