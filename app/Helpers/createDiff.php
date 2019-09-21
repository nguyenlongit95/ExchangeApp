<?php
namespace App\Helpers;
use DB;
use App\Modules\Exchanges\Models\NgoaiTe;
use App\Modules\Exchanges\Models\NgoaiTeCron;
class createDiff{
    /**
     * Thống kê tăng giảm bao nhiêu % đối với các tỷ giá
     * */
    public function tygiaNow($code, $bank_id, $NgoaiTe, $NgoaiTeCron)
    {
        if ($code != null && $bank_id != null) {
            $tygiacu = NgoaiTe::where("code", "=", $code)
                ->where("bank_id", "=", $bank_id)
                ->orderBy('id', 'DESC')
                ->first();

            if ($tygiacu) {
                if ($tygiacu->muatienmat != null) {
                    if($NgoaiTe->muatienmat != 0 || $NgoaiTe->muatienmat != null){
                        $tyle_muatienmat = (float) $NgoaiTe->muatienmat-$tygiacu->muatienmat;
                    } else {
                        $tyle_muatienmat = null;
                    }
                } else {
                    $tyle_muatienmat = null;
                }
                if ($tygiacu->muachuyenkhoan != null) {
                    if($NgoaiTe->muachuyenkhoan != 0 || $NgoaiTe->muachuyenkhoan != null){
                        $tyle_muachuyenkhoan = (float) $NgoaiTe->muachuyenkhoan-$tygiacu->muachuyenkhoan;
                    }else{
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_muachuyenkhoan = null;
                }
                if ($tygiacu->bantienmat != null) {
                    if($NgoaiTe->bantienmat != 0 || $NgoaiTe->bantienmat != null){
                        $tyle_bantienmat = (float) $NgoaiTe->bantienmat-$tygiacu->bantienmat;
                    } else{
                        $tyle_bantienmat = null;
                    }
                } else {
                    $tyle_bantienmat = null;
                }
                if ($tygiacu->banchuyenkhoan != null) {
                    if($NgoaiTe->banchuyenkhoan != 0 || $NgoaiTe->banchuyenkhoan != null){
                        $tyle_banchuyenkhoan = (float) $NgoaiTe->banchuyenkhoan-$tygiacu->banchuyenkhoan;
                    } else {
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_banchuyenkhoan = null;
                }
                $check = DB::table('ngoaite_today')
                    ->where('code','=',$code)
                    ->where('bank_id','=', $bank_id)
                    ->orderBy('id','DESC')
                    ->delete();

                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id"=>$NgoaiTeCron,
                    "code"=>$code,
                    'bank_id'=>$bank_id,
                    'bank_code'=>$NgoaiTe->bank_code,
                    'bank_name'=>$NgoaiTe->bank_name,
                    'bank_image'=>$NgoaiTe->bank_image,
                    'symbol'=>$NgoaiTe->symbol,
                    'image'=>$NgoaiTe->image,
                    'vname'=>$NgoaiTe->vname,
                    'ename'=>$NgoaiTe->ename,
                    'muatienmat'=>$NgoaiTe->muatienmat,
                    'tyle_muatienmat'=>$tyle_muatienmat,
                    'muachuyenkhoan'=>$NgoaiTe->muachuyenkhoan,
                    'tyle_muachuyenkhoan'=>$tyle_muachuyenkhoan,
                    'bantienmat'=>$NgoaiTe->bantienmat,
                    'tyle_bantienmat'=>$tyle_bantienmat,
                    'banchuyenkhoan'=>$NgoaiTe->banchuyenkhoan,
                    'tyle_banchuyenkhoan'=>$tyle_banchuyenkhoan,
                    'date'=>$NgoaiTe->date,
                    'time'=>$NgoaiTe->time
                ]);
                if($NgoaiTeToDay){
                    $arr_diff = array(
                        "tyle_muatienmat"=>$tyle_muatienmat,
                        "tyle_muachuyenkhoan"=>$tyle_muachuyenkhoan,
                        "tyle_bantienmat"=>$tyle_bantienmat,
                        "tyle_banchuyenkhoan"=>$tyle_banchuyenkhoan
                    );
                    return $arr_diff;
                }
            }else{
                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id"=>$NgoaiTeCron,
                    "code"=>$code,
                    'bank_id'=>$bank_id,
                    'bank_code'=>$NgoaiTe->bank_code,
                    'bank_name'=>$NgoaiTe->bank_name,
                    'bank_image'=>$NgoaiTe->bank_image,
                    'symbol'=>$NgoaiTe->symbol,
                    'image'=>$NgoaiTe->image,
                    'vname'=>$NgoaiTe->vname,
                    'ename'=>$NgoaiTe->ename,
                    'muatienmat'=>$NgoaiTe->muatienmat,
                    'tyle_muatienmat'=>null,
                    'muachuyenkhoan'=>$NgoaiTe->muachuyenkhoan,
                    'tyle_muachuyenkhoan'=>null,
                    'bantienmat'=>$NgoaiTe->bantienmat,
                    'tyle_bantienmat'=>null,
                    'banchuyenkhoan'=>$NgoaiTe->banchuyenkhoan,
                    'tyle_banchuyenkhoan'=>null,
                    'date'=>$NgoaiTe->date,
                    'time'=>$NgoaiTe->time
                ]);
                if($NgoaiTeToDay){
                    return null;
                }
            }
        }
    }
}

?>
