<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class LoaiTienAo extends Model
{
    use HasRoles;

    protected $table = 'loaitienao';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'name',
        'state',
        'symbol',
        'slug',
        'description',
        'icon',
        'created_at',
        'updated_at',
    ];
}
