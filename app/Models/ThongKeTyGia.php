<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class ThongKeTyGia extends Model
{
    use HasRoles;

    protected $table = 'thongke_tygia';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'code',
        'time_update',
        'percen_muatienmat',
        'percen_muachuyenkhoan',
        'percen_bantienmat',
        'percen_banchuyenkhoan',
        'created_at',
        'updated_at'
    ];
}
