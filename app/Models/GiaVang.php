<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class GiaVang extends Model
{
    use HasRoles;

    protected $table = 'tygiavang_data';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'cron_id',
        'tieude',
        'slug',
        'loai',
        'tinhthanh',
        'mua',
        'tyle_mua',
        'ban',
        'tyle_ban',
        'data',
        'created_at',
        'updated_at'
    ];
}
