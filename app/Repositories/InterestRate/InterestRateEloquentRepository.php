<?php
/*
 * Tại đây ta khai báo các phương thức cụ thể cho đối tượng
 * Class này sẽ extends EloquentRepository và Implements CateogryRepositoryInterface
 * */
namespace App\Repositories\InterestRate;

use App\Models\GiaVang;
use App\Models\LaiSuat;
use App\Repositories\Eloquent;
use App\Repositories\Eloquent\EloquentRepository;
use App\Email;
use Mail;
use DB;
use App\Models\NgoaiTe;

class InterestRateEloquentRepository extends EloquentRepository implements InterestRateRepositoryInterface
{
    public function getModel()
    {
        // TODO: Implement getModel() method.
        return LaiSuat::class;
    }
}

