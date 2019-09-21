<?php

namespace App\Models;

use App\Modules\Currency\Models\CurrencyCode;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class NgoaiTe extends Model
{
    use HasRoles;

    protected $table = 'ngoaite';
    //public $timestamps = false;
    protected $fillable = [
        'id',
        'cron_id',
        'code',
        'bank_id',
        'bank_code',
        'bank_name',
        'bank_image',
        'vname',
        'ename',
        'symbol',
        'image',
        'muatienmat',
        'muatienmat_diff',
        'bantienmat',
        'bantienmat_diff',
        'muachuyenkhoan',
        'muachuyenkhoan_diff',
        'banchuyenkhoan',
        'banchuyenkhoan_diff',
        'default',
        'date',
        'time',
    ];
    public static function create($data,$bank){

        if (!isset($data['code']) || !isset($data['cron_id'])){
            return "Dữ liệu không hợp lệ";
        }
        $ngoaite = new NgoaiTe();
        $currency = CurrencyCode::where('code',$data['code'])->first(['name','vname','icon','symbol']);
        if ($currency){
            $ngoaite->vname = $currency->vname;
            $ngoaite->ename = $currency->name;
            $ngoaite->image = $currency->icon;
            $ngoaite->symbol = $currency->symbol;
        }
        $ngoaite->cron_id = $data['cron_id'];
        $ngoaite->code = $data['code'];
        $ngoaite->bank_id = $bank->id;
        $ngoaite->bank_code = $bank->bankcode;
        $ngoaite->bank_name = $bank->bankname;
        $ngoaite->bank_image = $bank->logo;

        $ngoaite->muatienmat = isset($data['muatienmat']) ? $data['muatienmat']:null;
        $ngoaite->muachuyenkhoan = isset($data['muachuyenkhoan']) ? $data['muachuyenkhoan']:null;
        $ngoaite->bantienmat = isset($data['bantienmat']) ? $data['bantienmat']:null;
        $ngoaite->banchuyenkhoan = isset($data['banchuyenkhoan']) ? $data['banchuyenkhoan']:null;
        $ngoaite->time = Carbon::now()->toTimeString();
        $ngoaite->date = Carbon::now()->toDateString();
        try{
            $arr_diff = $ngoaite -> tygiaNow($ngoaite);
           if ($arr_diff){
               $ngoaite->muatienmat_diff = $arr_diff["tyle_muatienmat"];
               $ngoaite->bantienmat_diff = $arr_diff["tyle_bantienmat"];
               $ngoaite->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
               $ngoaite->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
           }
            $ngoaite->save();
            return $ngoaite;
        }catch (\Exception $ex){
            dd($ex->getMessage());
            return false;
        }

    }
    public function tygiaNow($ngoaite)
    {
        if ($ngoaite->code != null && $ngoaite->bank_id != null) {
            $tygiacu = NgoaiTe::where("code", "=", $ngoaite->code)
                ->where("bank_id", "=", $ngoaite->bank_id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($tygiacu) {
                if ($tygiacu->muatienmat != null) {
                    if($ngoaite->muatienmat != 0 || $ngoaite->muatienmat != null){
                        $tyle_muatienmat = (float) $ngoaite->muatienmat-$tygiacu->muatienmat;
                    } else {
                        $tyle_muatienmat = null;
                    }
                } else {
                    $tyle_muatienmat = null;
                }
                if ($tygiacu->muachuyenkhoan != null) {
                    if($ngoaite->muachuyenkhoan != 0 || $ngoaite->muachuyenkhoan != null){
                        $tyle_muachuyenkhoan = (float) $ngoaite->muachuyenkhoan-$tygiacu->muachuyenkhoan;
                    }else{
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_muachuyenkhoan = null;
                }
                if ($tygiacu->bantienmat != null) {
                    if($ngoaite->bantienmat != 0 || $ngoaite->bantienmat != null){
                        $tyle_bantienmat = (float) $ngoaite->bantienmat-$tygiacu->bantienmat;
                    } else{
                        $tyle_bantienmat = null;
                    }
                } else {
                    $tyle_bantienmat = null;
                }
                if ($tygiacu->banchuyenkhoan != null) {
                    if($ngoaite->banchuyenkhoan){
                        $tyle_banchuyenkhoan = (float) $ngoaite->banchuyenkhoan-$tygiacu->banchuyenkhoan;
                    } else {
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_banchuyenkhoan = null;
                }
                $check = DB::table('ngoaite_today')
                    ->where('code','=',$ngoaite->code)
                    ->where('bank_id','=', $ngoaite->bank_id)
                    ->orderBy('id','DESC')
                    ->delete();
                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id"=>$ngoaite->cron_id,
                    "code"=>$ngoaite->code,
                    'bank_id'=>$ngoaite->bank_id,
                    'bank_code'=>$ngoaite->bank_code,
                    'bank_name'=>$ngoaite->bank_name,
                    'bank_image'=>$ngoaite->bank_image,
                    'symbol'=>$ngoaite->symbol,
                    'image'=>$ngoaite->image,
                    'vname'=>strip_tags($ngoaite->vname),
                    'ename'=>strip_tags($ngoaite->ename),
                    'muatienmat'=>$ngoaite->muatienmat,
                    'tyle_muatienmat'=>isset($tyle_muatienmat) ? $tyle_muatienmat:null,
                    'muachuyenkhoan'=>$ngoaite->muachuyenkhoan,
                    'tyle_muachuyenkhoan'=>isset($tyle_muachuyenkhoan) ? $tyle_muachuyenkhoan:null,
                    'bantienmat'=>$ngoaite->bantienmat,
                    'tyle_bantienmat'=>isset($tyle_bantienmat) ? $tyle_bantienmat:null,
                    'banchuyenkhoan'=>$ngoaite->banchuyenkhoan,
                    'tyle_banchuyenkhoan'=>isset($tyle_banchuyenkhoan) ? $tyle_banchuyenkhoan:null,
                    'date'=>$ngoaite->date,
                    'time'=>$ngoaite->time
                ]);
                if($NgoaiTeToDay){
                    $arr_diff = array(
                        "tyle_muatienmat"=>isset($tyle_muatienmat) ? $tyle_muatienmat:null,
                        "tyle_muachuyenkhoan"=>isset($tyle_muachuyenkhoan) ? $tyle_muachuyenkhoan:null,
                        "tyle_bantienmat"=>isset($tyle_bantienmat) ? $tyle_bantienmat:null,
                        "tyle_banchuyenkhoan"=>isset($tyle_banchuyenkhoan) ? $tyle_banchuyenkhoan:null,
                    );
                    return $arr_diff;
                }
            }else{
                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id"=>$ngoaite->cron_id,
                    "code"=>$ngoaite->code,
                    'bank_id'=>$ngoaite->bank_id,
                    'bank_code'=>$ngoaite->bank_code,
                    'bank_name'=>$ngoaite->bank_name,
                    'bank_image'=>$ngoaite->bank_image,
                    'symbol'=>$ngoaite->symbol,
                    'image'=>$ngoaite->image,
                    'vname'=>$ngoaite->vname,
                    'ename'=>$ngoaite->ename,
                    'muatienmat'=>$ngoaite->muatienmat,
                    'tyle_muatienmat'=>null,
                    'muachuyenkhoan'=>$ngoaite->muachuyenkhoan,
                    'tyle_muachuyenkhoan'=>null,
                    'bantienmat'=>$ngoaite->bantienmat,
                    'tyle_bantienmat'=>null,
                    'banchuyenkhoan'=>$ngoaite->banchuyenkhoan,
                    'tyle_banchuyenkhoan'=>null,
                    'date'=>$ngoaite->date,
                    'time'=>$ngoaite->time
                ]);
                if($NgoaiTeToDay){
                    return null;
                }
            }
        }
    }
}
