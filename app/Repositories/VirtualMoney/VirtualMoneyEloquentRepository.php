<?php
/*
 * Tại đây ta khai báo các phương thức cụ thể cho đối tượng
 * Class này sẽ extends EloquentRepository và Implements CateogryRepositoryInterface
 * */
namespace App\Repositories\GoldExchanges;

use App\Models\GiaVang;
use App\Repositories\Eloquent;
use App\Repositories\Eloquent\EloquentRepository;
use App\Email;
use Mail;
use DB;
use App\Models\NgoaiTe;

class VirtualMoneyEloquentRepository extends EloquentRepository implements GoldExchangeRepositoryInterface
{
    public function getModel()
    {
        // TODO: Implement getModel() method.
        return GiaVang::class;
    }
}

