<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected  $table='campaign';

    protected $fillable = [
        'campaign_id','conditions','discount_type','max_condition','min_condition','gift_condition','campaign_name'
    ];
    protected $casts=
        [
            'conditions'=>'array',
        ];
}
