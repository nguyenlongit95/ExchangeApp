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
                    $ngoaite->muatienmat = number_format($ngoaite->muatienmat, 0);
                    $ngoaite->muatienmat_diff = number_format($ngoaite->muatienmat_diff, 0);
                    $ngoaite->bantienmat = number_format($ngoaite->bantienmat, 0);
                    $ngoaite->bantienmat_diff = number_format($ngoaite->bantienmat_diff, 0);
                    $ngoaite->muachuyenkhoan = number_format($ngoaite->muachuyenkhoan, 0);
                    $ngoaite->muachuyenkhoan_diff = number_format($ngoaite->muachuyenkhoan_diff, 0);
                    $ngoaite->banchuyenkhoan = number_format($ngoaite->banchuyenkhoan, 0);
                    $ngoaite->banchuyenkhoan_diff = number_format($ngoaite->banchuyenkhoan_diff, 0);
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
                $exchange->muatienmat = number_format($exchange->muatienmat, 0);
                $exchange->muatienmat_diff = number_format($exchange->muatienmat_diff, 0);
                $exchange->bantienmat = number_format($exchange->bantienmat, 0);
                $exchange->bantienmat_diff = number_format($exchange->bantienmat_diff, 0);
                $exchange->muachuyenkhoan = number_format($exchange->muachuyenkhoan, 0);
                $exchange->muachuyenkhoan_diff = number_format($exchange->muachuyenkhoan_diff, 0);
                $exchange->banchuyenkhoan = number_format($exchange->banchuyenkhoan, 0);
                $exchange->banchuyenkhoan_diff = number_format($exchange->banchuyenkhoan_diff, 0);
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
