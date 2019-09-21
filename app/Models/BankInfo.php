<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    use HasRoles;

    protected $table = 'bank_info';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'bankname',
        'logo',
        'description',
        'bankcode',
        'bankslug',
        'active',
        'sort',
        'created_at',
        'updated_at'
    ];
}
