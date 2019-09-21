<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class LaiSuatThongKe extends Model
{
    use HasRoles;

    protected $table = 'laisuat_thongke';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'timeupdate',
        'bank_id',
        'tygiatrungbinh_cao',
        'tygiatrungbinh_thap',
        'biendong',
        'created_at',
        'updated_at'
    ];
}
