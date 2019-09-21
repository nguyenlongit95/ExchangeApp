<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class TienAo extends Model
{
    use HasRoles;

    protected $table = 'tienao';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'name',
        'symbol',
        'slug',
        'circulating_supply',
        'total_supply',
        'max_supply',
        'date_added',
        'num_market_pairs',
        'last_updated',
        'price',
        'volume_24h',
        'percent_change_1h',
        'percent_change_24h',
        'percent_change_7d',
        'price_vn',
        'volume_vn_24h',
        'percent_change_vn_1h',
        'percent_change_vn_24h',
        'percent_change_vn_7d',
        'market_cap',
        'rank',
        'created_at',
        'updated_at',
    ];



}
