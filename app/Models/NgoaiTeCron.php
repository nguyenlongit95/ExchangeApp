<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class NgoaiTeCron extends Model
{
    use HasRoles;

    protected $table = 'ngoaite_cron';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'cronkey',
        'created_at',
        'updated_at',
    ];
}
