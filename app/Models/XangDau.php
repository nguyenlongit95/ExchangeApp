<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class XangDau extends Model
{
    use HasRoles;

    protected $table = 'xangdau';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'ten',
        'slug',
        'giavung1',
        'giavung2',
        'created_at',
        'updated_at',
    ];
}
