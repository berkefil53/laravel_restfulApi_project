<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(storage_path('app/campaign.json'));
        $data = json_decode($json);

        foreach ($data as $item) {
            $conditions = json_encode($item->conditions); // "conditions" alanını JSON formatında çözümle

            DB::table('campaign')->insert([
                'campaign_id' => $item->campaign_id,
                'conditions' => $conditions,
                'discount_type' => $item->discount_type,
                'max_condition' => $item->max_condition,
                'min_condition' => $item->min_condition,
                'gift_condition' => $item->gift_condition,
                'campaign_name' => $item->campaign_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
