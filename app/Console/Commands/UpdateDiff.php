<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\NgoaiTe;
use App\Helpers\checkSymBol;

class UpdateDiff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diff:updatediff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ngoaiTeCron = DB::table('ngoaite_cron')->where('id', '>', 973)->orderBy('id', 'ASC')->select('id')->get();

        foreach ($ngoaiTeCron as $cronID) {
            $TyGiaCu = DB::table('ngoaite')->where('cron_id', $cronID->id)->get();

            $CronSau = $cronID->id + 1;
            $TyGiaSau = DB::table('ngoaite')->where('cron_id', $CronSau)->get();

            foreach ($TyGiaSau as $tygiasau) {
                foreach ($TyGiaCu as $tygiacu) {
                    if ($tygiacu->code == $tygiasau->code && $tygiacu->bank_id == $tygiasau->bank_id) {

                        if ($tygiacu->muatienmat != null && $tygiacu->muatienmat != 0) {
                            $muatienmat_diff = $tygiasau->muatienmat - $tygiacu->muatienmat;
                        } else {
                            $muatienmat_diff = null;
                        }
                        if ($tygiacu->bantienmat != null && $tygiacu->bantienmat != 0) {
                            $bantienmat_diff = $tygiasau->bantienmat - $tygiacu->bantienmat;
                        } else {
                            $bantienmat_diff = null;
                        }
                        if ($tygiacu->muachuyenkhoan != null && $tygiacu->muachuyenkhoan != 0) {
                            $muachuyenkhoan_diff = $tygiasau->muachuyenkhoan - $tygiacu->muachuyenkhoan;
                        } else {
                            $muachuyenkhoan_diff = null;
                        }
                        if ($tygiacu->banchuyenkhoan != null && $tygiacu->banchuyenkhoan != 0) {
                            $banchuyenkhoan_diff = $tygiasau->banchuyenkhoan - $tygiacu->banchuyenkhoan;
                        } else {
                            $banchuyenkhoan_diff = null;
                        }
                        echo $this->updateDiff($muatienmat_diff, $bantienmat_diff, $muachuyenkhoan_diff, $banchuyenkhoan_diff, $CronSau, $tygiasau->bank_id, $tygiasau->code);
                    } else {
                        continue;
                    }
                }
            }
        }

        // $this->capnhatngoaite();

    }
    // Function update tygia_diff
    private function updateDiff($muatienmat_diff, $bantienmat_diff, $muachuyenkhoan_diff, $banchuyenkhoan_diff, $cron_id, $bank_id, $code)
    {
        $checkSymBol = new checkSymBol();
        if (!$muatienmat_diff) {
            $muatienmat_diff = null;
        }
        if (!$bantienmat_diff) {
            $bantienmat_diff = null;
        }
        if (!$muachuyenkhoan_diff) {
            $muachuyenkhoan_diff = null;
        }
        if (!$banchuyenkhoan_diff) {
            $banchuyenkhoan_diff = null;
        } else {
            $Bank = DB::table('bank_info')->where('id', $bank_id)->first();
            // dd($Bank);
            $Update = DB::table('ngoaite')->where('bank_id', $bank_id)
                ->where('cron_id', $cron_id)->where('code', $code)->update([
                    'muatienmat_diff' => $muatienmat_diff,
                    'bantienmat_diff' => $bantienmat_diff,
                    'muachuyenkhoan_diff' => $muachuyenkhoan_diff,
                    'banchuyenkhoan_diff' => $banchuyenkhoan_diff,
                    'symbol' => $checkSymBol->checkSymbol($code),
                    'image' => '/storage/currency/' . $code . '.png',
                    'bank_code' => $Bank->bankcode,
                    'bank_name' => $Bank->bankname,
                    'bank_image' => $Bank->logo
                ]);
            if ($Update) {
                return "Cập nhật tỷ lệ của đồng: " . $code . " tại ngân hàng id: " . $bank_id . " với cron: " . $cron_id . " thành công <br/>";
            } else {
                return "Cập nhật tỷ lệ của đồng: " . $code . " tại ngân hàng id: " . $bank_id . " với cron: " . $cron_id . " có lỗi, hãy kiểm tra lại! <br/>";
            }
        }
    }


    public function capnhatngoaite()
    {

        ini_set('max_execution_time', 3000);

        $bank = DB::table('bank_info')->pluck('bankcode');
        $checkSymbol = new checkSymBol();
        $cur = array();
        $list_cu = NgoaiTe::get();
        foreach ($list_cu as $cu) {
            if (!in_array($cu->code, $cur)) {
                $cur[] = $cu->code;
            }
        }

        foreach ($bank as $bk) {

            foreach ($cur as $ci) {
                $list = NgoaiTe::where('code', $ci)->where('bank_code', $bk)->orderBy('id', 'asc')->get();

                foreach ($list as $key => $lit) {

                    $muatienmat_diff =  0;
                    $bantienmat_diff =  0;
                    $muachuyenkhoan_diff =  0;
                    $banchuyenkhoan_diff =  0;

                    $nid[$key] = $lit->id;
                    $sl = $key - 1;
                    if ($sl >= 0) {
                        $secondlast = NgoaiTe::find($nid[$sl]);

                        if ($secondlast) {
                            $muatienmat_diff =  $lit->muatienmat - $secondlast->muatienmat;
                            $bantienmat_diff =  $lit->bantienmat - $secondlast->bantienmat;
                            $muachuyenkhoan_diff =  $lit->muachuyenkhoan - $secondlast->muachuyenkhoan;
                            $banchuyenkhoan_diff =  $lit->banchuyenkhoan - $secondlast->banchuyenkhoan;

                            // $muatienmat_diff =  ($secondlast->muatienmat == 0 || $lit->muatienmat == 0) ? 0 : ($lit->muatienmat - $secondlast->muatienmat);
                            // $bantienmat_diff =  ($secondlast->bantienmat == 0 || $lit->bantienmat == 0) ? 0 : ($lit->bantienmat - $secondlast->bantienmat);
                            // $muachuyenkhoan_diff =  ($secondlast->muachuyenkhoan !== 0 || $lit->muachuyenkhoan !== 0) ? 0 : ($lit->muachuyenkhoan - $secondlast->muachuyenkhoan);
                            // $banchuyenkhoan_diff =  ($secondlast->banchuyenkhoan !== 0 || $lit->banchuyenkhoan !== 0) ? 0 : ($lit->banchuyenkhoan - $secondlast->banchuyenkhoan);
                        }
                    }

                    $lit->muatienmat_diff = $muatienmat_diff;
                    $lit->bantienmat_diff = $bantienmat_diff;
                    $lit->muachuyenkhoan_diff = $muachuyenkhoan_diff;
                    $lit->banchuyenkhoan_diff = $banchuyenkhoan_diff;
                    $list->bank_code = $bk;
                    $list->bank_name = $bk;
                    $list->bank_image = "/storage/userfiles/images/icons/" . $bk . ".png";
                    $list->symbol = $checkSymbol->checkSymbol($list->code);
                    $list->image = "/storage/currency/" . $list->code . ".png";
                    $lit->update();
                }
            }
        }
    }
}
