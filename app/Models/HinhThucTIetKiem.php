<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class HinhThucTIetKiem extends Model
{
    use HasRoles;

    protected $table = 'hinhthuc_tietkiem';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'hinhthuc',
        'created_at',
        'updated_at'
    ];
}
