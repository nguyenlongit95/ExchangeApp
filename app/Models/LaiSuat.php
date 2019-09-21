<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class LaiSuat extends Model
{
    use HasRoles;

    protected $table = 'lai_suat';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'bank_id',
        'bank_name',
        'bank_code',
        'hinhthuctietkiem',
        'kyhan',
        'kyhanslug',
        'moctiengui',
        'moctienguisau',
        'laisuat_vnd',
        'laisuat_eur',
        'laisuat_usd',
        'laisuattratruoc',
        'laisuathangthang',
        'laisuatcuoiky',
        'laisuathangquy',
        'created_at',
        'updated_at'
    ];
}
