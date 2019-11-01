<?php
/*
 * Tại đây ta khai báo các phương thức cụ thể cho đối tượng
 * Class này sẽ extends EloquentRepository và Implements CateogryRepositoryInterface
 * */
namespace App\Repositories\Exchanges;

use App\Repositories\Eloquent;
use App\Repositories\Eloquent\EloquentRepository;
use App\Email;
use Mail;
use DB;
use App\Models\NgoaiTe;

class ExchangeEloquentRepository extends EloquentRepository implements ExchangeRepositoryInterface
{

    /**
     * merge this exchanges
     *  param bank information and list data exchange
     *
     *  return collection
     */
    public function mergeExchange($bankInfo, $ngoaiTe)
    {
        foreach ($ngoaiTe as $ngoaite) {
            if (!$ngoaite->bank_id) {
                continue;
            }
            foreach ($bankInfo as $bank) {
                if ($bank->id == $ngoaite->bank_id) {
                    $ngoaite->bank_name = $bank->bankname;
                    $ngoaite->bank_code = $bank->bankcode;
                } else {
                    continue;
                }
            }
        }
        return $ngoaiTe;
    }

    public function mergeExchangeOfBank($bankInfo, $exchanges)
    {
        foreach ($exchanges as $exchange) {
            if ($exchange->bank_id == $bankInfo->id) {
                $exchange->bank_name = $bankInfo->bankname;
                $exchange->bank_code = $bankInfo->bankcode;
            } else {
                continue;
            }
        }
        return $exchanges;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return NgoaiTe::class;
    }
}

?>
