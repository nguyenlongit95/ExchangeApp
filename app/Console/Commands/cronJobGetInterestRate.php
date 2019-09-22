<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Helpers\SimpleHtmlDom;
use Carbon\Carbon;
use App\Models\LaiSuat;
use App\Helpers\ChangeText;

class cronJobGetInterestRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronJob:getInterestRate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lay lai suat cua cac ngan hang';

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
        $SimpleHTMLDOM = new SimpleHtmlDom();
        $changeText = new ChangeText();
        $urlVCB = "https://www.vietcombank.com.vn/InterestRates/";
        try{
            $htmlVCB = $SimpleHTMLDOM->file_get_html($urlVCB);
        }catch(\Exception $e){
            $htmlVCB = null;
        }
        $this->vietcombank($htmlVCB);

        $urlVTB = "https://www.vietinbank.vn/web/home/vn/lai-suat";
        try{
            $htmlVTB = $SimpleHTMLDOM->file_get_html($urlVTB);
        }catch(\Exception $e){
            $htmlVTB = null;
        }
        $this->vtb($htmlVTB);

    //    $urlSHB = "https://www.shb.com.vn/category/lien-ket-nhanh/lai-suat-tiet-kiem/";
    //    try{
    //        $htmlSHB = $SimpleHTMLDOM->file_get_html($urlSHB);
    //    }catch(\Exception $e){
    //        $htmlSHB = null;
    //    }

        // $this->shb($SimpleHTMLDOM);

        $this->bidv();

        $urlDONGA = "http://kinhdoanh.dongabank.com.vn/widget/temp/-/DTSCDongaBankIView_WAR_DTSCDongaBankIERateportlet?type=tktt-vnd";
        try{
            $htmlDONGA = $SimpleHTMLDOM->file_get_html($urlDONGA);
        }catch(\Exception $e){
            $htmlDONGA = null;
        }
        $this->donga($htmlDONGA);

        $url = "http://oceanbank.vn/lai-suat.html";
        try{
            $htmlocean = $SimpleHTMLDOM->file_get_html($url);
        }catch(\Exception $e){
            $htmlocean = null;
        }
        $this->OceanBank($htmlocean);

        $urlscb = "https://www.saigonbank.com.vn/vi/truy-cap-nhanh/lai-suat";
        try{
            $htmlscb = $SimpleHTMLDOM->file_get_html($urlscb);
        }catch(\Exception $exception){
            $htmlscb = null;
        }
        $this->scb($htmlscb);

        $urlMB = "https://webgia.com/lai-suat/mbbank/";
        try{
            $htmlMB = $SimpleHTMLDOM->file_get_html($urlMB);
        }catch(\Exception $exception){
            $htmlMB = null;
        }
        $this->lsMbbank($htmlMB);
        $this->ocb($SimpleHTMLDOM);
        $this->vib($SimpleHTMLDOM);
        $this->baoviet($SimpleHTMLDOM);
        $this->laisuatnongnghiep($SimpleHTMLDOM);
        $this->ncb($SimpleHTMLDOM);


        //Laisuat online
        $urlOnlineBank = "https://webgia.com/lai-suat/";
        try{
            $htmlOnlineBank = $SimpleHTMLDOM->file_get_html($urlOnlineBank);
        }catch(\Exception $exception){
            $htmlOnlineBank = null;
        }
        $this->getOnlineLSBank($htmlOnlineBank);
    }

    protected function vietcombank($html){

        if($html){
            $changeText = new ChangeText();
            $rows = $html->find("table.tbl-01 tbody tr");
            if(count($rows) > 1){
                for($i = 2; $i <= 13; $i++){
                    $LaiSuat = new LaiSuat();
                    $LaiSuat->bank_id = 8;
                    $LaiSuat->bank_code = 'vietcombank';
                    $LaiSuat->bank_name = "Vietcombank";
                    $LaiSuat->hinhthuctietkiem = 1;
                    $LaiSuat->kyhan = $rows[$i]->find("td",0)->innertext;
                    if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($rows[$i]->find("td",0)->innertext))) === "khong-ky-han"){
                        $LaiSuat->kyhanslug = 0;
                    }else{
                        $LaiSuat->kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($rows[$i]->find("td",0)->innertext))));
                    }
                    $LaiSuat->moctiengui = null;
                    $LaiSuat->laisuat_vnd = floatval($this->changeText($rows[$i]->find("td",1)->innertext));
                    $LaiSuat->laisuat_eur = floatval($this->changeText($rows[$i]->find("td",2)->innertext));
                    $LaiSuat->laisuat_usd = floatval($this->changeText($rows[$i]->find("td",3)->innertext));
                    $LaiSuat->laisuattratruoc = null;
                    $LaiSuat->laisuathangthang = null;
                    $LaiSuat->save();
                }
            }else{
                return response()->json(["message","Cannot find table data"]);
            }

        }else{
        }

    }

    protected function bidv(){

        $url = "https://www.bidv.com.vn/ServicesBIDV/InterestRateVIServlet";

        try{

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
            $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            curl_setopt($ch, CURLOPT_REFERER, $actual_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
            $result = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($result);

        }catch(\Exception $e){
            $responseData = null;
        }

        if($responseData){
            $changeText = new ChangeText();
            $LaiSuat = $responseData->data;

            foreach($LaiSuat as $laisuat){
                $ls = new LaiSuat();
                $ls->bank_id = 5;
                $ls->bank_code = 'bidv';
                $ls->bank_name = "BIDV";
                $ls->hinhthuctietkiem = 1;
                $ls->kyhan = $laisuat->title;
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($laisuat->title))) === "khong-ky-han"){
                    $ls->kyhanslug = 0;
                }else{
                    $ls->kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($laisuat->title))));
                }
                $ls->moctiengui = null;
                $ls->moctienguisau = null;
                $ls->laisuat_vnd = $laisuat->VI;
                $ls->laisuat_eur = $laisuat->EUR;
                $ls->laisuat_usd = $laisuat->EN;
                $ls->laisuattratruoc = null;
                $ls->laisuathangthang = null;
                $ls->save();
            }

        }else{
        }

    }

    protected function vtb($html){

        if($html){
            $changeText = new ChangeText();
            $rows = $html->find("div#articles table tr");
            for($i = 3; $i < 22; $i++){
                $LaiSuat = new LaiSuat();
                $LaiSuat->bank_id = 6;
                $LaiSuat->bank_code = 'vietin';
                $LaiSuat->bank_name = "VietinBank";
                $LaiSuat->hinhthuctietkiem = 1;
                $LaiSuat->kyhan = strip_tags($rows[$i]->find("td",0));
                $kyhanslug = $this->changeKyHanSlugVTB(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($rows[$i]->find("td",0))))));
                if($kyhanslug === "khong-ky-han"){
                    $kyhanslug = 0;
                }
                $LaiSuat->kyhanslug = $kyhanslug;
                $LaiSuat->moctiengui = null;
                $LaiSuat->laisuat_vnd = floatval($this->changeText($rows[$i]->find("td",1)->innertext));
                $LaiSuat->laisuat_eur = floatval($this->changeText($rows[$i]->find("td",3)->innertext));
                $LaiSuat->laisuat_usd = floatval($this->changeText($rows[$i]->find("td",4)->innertext));
                $LaiSuat->laisuattratruoc = null;
                $LaiSuat->laisuathangthang = null;
                $LaiSuat->save();
            }
            // Tiến kiệm online
            for($i = 3; $i < 22; $i++){
                $LaiSuat = new LaiSuat();
                $LaiSuat->bank_id = 6;
                $LaiSuat->bank_code = 'vietin';
                $LaiSuat->bank_name = "VietinBank";
                $LaiSuat->hinhthuctietkiem = 2;
                $LaiSuat->kyhan = strip_tags($rows[$i]->find("td",0));
                $kyhanslug = $this->changeKyHanSlugVTB(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($rows[$i]->find("td",0))))));
                $LaiSuat->moctiengui = null;
                $LaiSuat->kyhanslug = $kyhanslug;
                $LaiSuat->laisuat_vnd = floatval($this->changeText($rows[$i]->find("td",2)->innertext));
                $LaiSuat->laisuat_eur = floatval($this->changeText($rows[$i]->find("td",3)->innertext));
                $LaiSuat->laisuat_usd = floatval($this->changeText($rows[$i]->find("td",4)->innertext));
                $LaiSuat->laisuattratruoc = null;
                $LaiSuat->laisuathangthang = null;
                $LaiSuat->save();
            }
        }else{
        }

    }

    protected function changeKyHanSlugVTB($kyhanslug){
        if($kyhanslug == "-khong-ky-ha-n"){
            return 0;
        }else if($kyhanslug == "-duo-i-1-tha-ng"){
            return 1;
        }else if($kyhanslug == "-tu-1-tha-ng-de-n-duoi-2-tha-ng-"){
            return 2;
        }else if($kyhanslug == "-tu-2-tha-ng-de-n-duoi-3-tha-ng-"){
            return 3;
        }else if($kyhanslug == "-tu-3-tha-ng-de-n-duoi-4-tha-ng-"){
            return 4;
        }else if($kyhanslug == "-tu-4-tha-ng-de-n-duoi-5-tha-ng-"){
            return 5;
        }else if($kyhanslug == "-tu-5-tha-ng-de-n-duoi-6-tha-ng-"){
            return 6;
        }else if($kyhanslug == "-tu-6-tha-ng-de-n-duoi-7-tha-ng-"){
            return 7;
        }else if($kyhanslug == "-tu-7-tha-ng-de-n-duoi-8-tha-ng-"){
            return 8;
        }else if($kyhanslug == "-tu-8-tha-ng-de-n-duoi-9-tha-ng-"){
            return 9;
        }else if($kyhanslug == "-tu-9-tha-ng-de-n-duoi-10-tha-ng-"){
            return 10;
        }else if($kyhanslug == "-tu-10-tha-ng-de-n-duoi-11-tha-ng-"){
            return 11;
        }else if($kyhanslug == "-tu-11-tha-ng-de-n-duoi-12-"){
            return 12;
        }else if($kyhanslug == "12"){
            return 12;
        }else if($kyhanslug == "-tren-12-tha-ng-de-n-duoi-18-tha-ng-"){
            return 18;
        }else if($kyhanslug == "-tu-18-tha-ng-de-n-duoi-24-tha-ng-"){
            return 24;
        }else if($kyhanslug == "-tu-24-tha-ng-de-n-duoi-36-tha-ng"){
            return 36;
        }else if($kyhanslug == "36-tha-ng"){
            return 36;
        }else if($kyhanslug == "tren-36"){
            return 48;
        }
    }

    protected function donga($html){

        if($html){
            $changeText = new ChangeText();
            $rows = $html->find('table[class=table table-bordered table-striped table-hover tktt-vnd] tr');

            for($i = 1; $i < count($rows); $i++){
                $LaiSuat = new LaiSuat();
                $LaiSuat->bank_id = 9;
                $LaiSuat->bank_code = 'donga';
                $LaiSuat->bank_name = 'DongA';
                $LaiSuat->hinhthuctietkiem = 1;
                $LaiSuat->kyhan = strip_tags($rows[$i]->find('td',0)->innertext);
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($rows[$i]->find("td",0)))))){
                    $LaiSuat->kyhanslug = 0;
                }else{
                    $LaiSuat->kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($rows[$i]->find("td",0))))));
                }
                $LaiSuat->moctiengui = null;
                $LaiSuat->moctienguisau = null;
                if($rows[$i]->find('td',1) == null || $rows[$i]->find('td',1) == ""){
                    $LaiSuat->laisuat_vnd = null;
                }else{
                    $LaiSuat->laisuat_vnd = floatval(str_replace(',', '.', str_replace('.', '', strip_tags($rows[$i]->find('td',1)->innertext))));
                }
                if($rows[$i]->find('td',2) == null || $rows[$i]->find('td',1) == ""){
                    $LaiSuat->laisuattratruoc = null;
                }else{
                    $LaiSuat->laisuattratruoc = floatval(str_replace(',', '.', str_replace('.', '', strip_tags($rows[$i]->find('td',2)->innertext))));
                }
                if($rows[$i]->find('td',3) == null || $rows[$i]->find('td',1) == ""){
                    $LaiSuat->laisuathangthang = null;
                }else{
                    $LaiSuat->laisuathangthang = floatval(str_replace(',', '.', str_replace('.', '', strip_tags($rows[$i]->find('td',3)->innertext))));
                }
                $LaiSuat->save();
            }

        }else{
        }

    }

    protected function changeText($str){
        $ChangePercentage = str_replace('%','', $str);
        $ChangeComma = str_replace(',','.', $ChangePercentage);
        $ChangeDash = str_replace('-','',$ChangeComma);
        return $ChangeDash;
    }
    protected function changeTextSymbol($str){
        return str_replace('','',$str);
    }

    protected function OceanBank($html)
    {

        if($html) {
            $changeText = new ChangeText();
            $table = $html->find('div#tabs1 ul.list_ls li div.ct_lstk table');

            for ($i = 0; $i < 1; $i++) {

                for ($j = 2; $j < 63; $j += 3) {
                    $LaiSuat = new LaiSuat();
                    $LaiSuat->bank_id = 11;
                    $LaiSuat->bank_code = 'ocean';
                    $LaiSuat->bank_name = 'OceanBank';
                    $LaiSuat->hinhthuctietkiem = 1;
                    $LaiSuat->kyhan = strip_tags($this->changeTxtOCB($table[$i]->find('tr td', $j - 2)->innertext));
                    $kyhanslug = html_entity_decode(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($this->changeTxtOCB($table[$i]->find('tr td', $j - 2)->innertext))))));
                    if($kyhanslug === "tgtt-tkkkh"){
                        $kyhanslug = 0;
                    }
                    $LaiSuat->kyhanslug = $kyhanslug;
                    $LaiSuat->moctiengui = null;
                    $LaiSuat->moctienguisau = null;
                    $LaiSuat->laisuat_vnd = floatval(str_replace(',', '.', strip_tags($table[$i]->find('tr td', $j - 1)->innertext)));
                    $LaiSuat->laisuat_usd = floatval(str_replace(',', '.', strip_tags($table[$i]->find('tr td', $j)->innertext)));
                    $LaiSuat->laisuat_eur = null;
                    $LaiSuat->laisuattratruoc = null;
                    $LaiSuat->laisuathangthang = null;
                    $LaiSuat->save();
                }

            }
        }
    }
    protected function changeTxtOCB($txt){
        return str_replace('th&aacute;ng','tháng',str_replace('v&agrave;','&',$txt));
    }

//    protected function mbbank($html){
//
//        if($html){
//            $changeText = new ChangeText();
//            $table = $html->find('table.ms-listviewtable',0);
//            $tbody = $table->find('tbody',5);
//            $tr = $tbody->find('tr');
//
//            for($i = 0; $i < count($tr); $i++){
//
//                $kyhan = strip_tags($tr[$i]->find('td',0)->innertext);
//                $laisuat_vnd = str_replace(',','',str_replace('%','',str_replace(' ','',$tr[$i]->find('td',1)->innertext)));
//
//                $Laisuat = DB::table('lai_suat')->insert([
//                    "bank_id"=>15,
//                    "hinhthuctietkiem"=>1,
//                    "kyhan"=>$kyhan,
//                    "kyhanslug"=>$changeText->changeTitle($kyhan),
//                    "moctiengui"=>null,
//                    "moctienguisau"=>null,
//                    "laisuat_vnd"=>$laisuat_vnd,
//                    "laisuat_eur"=> null,
//                    "laisuattratruoc"=>null,
//                    "laisuathangthang"=>null
//                ]);
//                if($Laisuat){
//                }else{
//                    dd("error");
//                }
//            }
//        }
//    }

    protected function scb($html){

        if($html) {
            $changeText = new ChangeText();
            $tr = $html->find('.table-responsive table tr');
            for($i = 4; $i < count($tr); $i++){
                $kyhan = html_entity_decode(str_replace(' ','',str_replace("\t",'',strip_tags($tr[$i]->find('td',0)))));
                $tralaicuoiky = str_replace(',','.',str_replace('%','',str_replace("\t","",str_replace(' ','',str_replace('&nbsp;','',strip_tags($tr[$i]->find('td',1)))))));
                $tralaihangquy = str_replace(',','.',str_replace('%','',str_replace("\t","",str_replace(' ','',str_replace('&nbsp;','',strip_tags($tr[$i]->find('td',2)))))));
                $laisuathangthang = str_replace(',','.',str_replace('%','',str_replace("\t","",str_replace(' ','',str_replace('&nbsp;','',strip_tags($tr[$i]->find('td',3)))))));
                $laisuattratruoc = str_replace(',','.',str_replace('%','',str_replace("\t","",str_replace(' ','',str_replace('&nbsp;','',strip_tags($tr[$i]->find('td',4)))))));

                if($tralaicuoiky == ''){
                    $tralaicuoiky = null;
                } if($tralaihangquy == ''){
                    $tralaihangquy = null;
                } if($laisuathangthang == ''){
                    $laisuathangthang = null;
                } if($laisuattratruoc == ''){
                    $laisuattratruoc = null;
                }

                if(str_replace('tuan','',str_replace('thang','',$changeText->changeTitle($kyhan))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('tuan','',str_replace('thang','',$changeText->changeTitle($kyhan))));
                }
                if($kyhanslug == "tietkiemcokyhan"){
                    continue;
                }
                $insertSCB = DB::table('lai_suat')->insert([
                    "bank_id"=>16,
                    "bank_code"=>"scb",
                    "bank_name"=>"SCB",
                    "hinhthuctietkiem"=>1,
                    "kyhan"=>$kyhan,
                    "kyhanslug"=>$kyhanslug,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuat_vnd"=>$laisuattratruoc,
                    "laisuattratruoc"=>$laisuattratruoc,
                    "laisuathangthang"=>$laisuathangthang,
                    "laisuatcuoiky"=>$tralaicuoiky,
                    "laisuathangquy"=> $tralaihangquy,
                ]);
                if($insertSCB){
                    echo "Thêm thành công lãi suất ngân hàng SCB: " . $kyhan . "\n";
                }else{
                    echo "Thêm lãi suất ngân hàng SCB: " . $kyhan . " thất bại \n";
                }
            }
        }
    }

    public function lsMbbank($html){

        $changeText = new ChangeText();
        if($html){
            $table = $html->find("div.table-responsive",0);
            $tr = $table->find("tr");
            for($i = 1; $i < count($tr); $i++){
                $kyhan = strip_tags($tr[$i]->find("td",0));
                $laisuat = strip_tags(str_replace('%','',str_replace(',','.',$tr[$i]->find("td",1))));
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($kyhan))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($kyhan))));
                }
                $insertMB = DB::table('lai_suat')->insert([
                    "bank_id"=>15,
                    "bank_code"=>"mbbank",
                    "bank_name"=>"MBank",
                    "hinhthuctietkiem"=>1,
                    "kyhan"=>$kyhan,
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($insertMB){
                    echo "Update Mbbank success";
                }else{
                    echo "Update Mbbank failed";
                }
            }
        }
    }



    /**
     * Lấy những ngân hàng:
     *  vietcombank
     *  oceanbank
     *  dongaBank
     *  SHB
     *  viettinBank
     *  bidv
     *  mbbank
     *  scb
     * */
    public function getOnlineLSBank($htmlOnlineBank){

        if($htmlOnlineBank){
            $changeText = new ChangeText();
            $table = $htmlOnlineBank->find(".table-responsive",1);
            $arrKyHan = array(
                "",
                "Không kỳ hạn",
                "01 tháng",
                "03 tháng",
                "06 tháng",
                "09 tháng",
                "12 tháng",
                "18 tháng",
                "24 tháng",
                "36 tháng"
            );
            // Vietcombank(ok)
            $trVCB = $table->find("tr", 16);
            $khongkyhan = $trVCB->find("td",1);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trVCB->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatVCB = DB::table('lai_suat')->insert([
                    "bank_id"=>8,
                    "bank_code"=>"vietcombank",
                    "bank_name"=>"Vietcombank",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatVCB){
                    echo "Lấy thành công dữ liệu VCB \n";
                }else{
                    echo "Có lỗi xảy ra, kiểm tra lại hệ thống \n";
                }
            }

            // OceanBank(ok)
            $trOceanBank = $table->find("tr", 12);
            $khongkyhan = $trOceanBank->find("td",1);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trOceanBank->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trOceanBank->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatVCB = DB::table('lai_suat')->insert([
                    "bank_id"=>11,
                    "bank_code"=>"ocean",
                    "bank_name"=>"OceanBank",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatVCB){
                    echo "Lấy thành công dữ liệu Ocean thành công \n";
                }else{
                    echo "Có lỗi xảy ra với OceanBank, kiểm tra lại hệ thống \n";
                }
            }

            // DongABank (ok)
            $trDongABank = $table->find("tr", 6);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trDongABank->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trDongABank->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatDongABank = DB::table('lai_suat')->insert([
                    "bank_id"=>9,
                    "bank_code"=>"donga",
                    "bank_name"=>"DongA",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatDongABank){
                    echo "Lấy thành công dữ liệu DongABank thành công \n";
                }else{
                    echo "Có lỗi xảy ra với DongABank, kiểm tra lại hệ thống \n";
                }
            }

            // SHB-13(ok)
            $trSHB = $table->find("tr", 14);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trSHB->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trSHB->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatSHB = DB::table('lai_suat')->insert([
                    "bank_id"=>3,
                    "bank_code"=>"shb",
                    "bank_name"=>"SHB",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatSHB){
                    echo "Lấy thành công dữ liệu SHB \n";
                }else{
                    echo "Có lỗi xảy ra với SHB, kiểm tra lại hệ thống \n";
                }
            }

            // VietinBank(ok)
            $trVietin = $table->find("tr", 17);
            for($i=1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trVietin->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVietin->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatVietin = DB::table('lai_suat')->insert([
                    "bank_id"=>6,
                    "bank_code"=>"vietin",
                    "bank_name"=>"VietinBank",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatVietin){
                    echo "Lấy thành công dữ liệu VietinBank \n";
                }else{
                    echo "Có lỗi xảy ra với Vietin, kiểm tra lại hệ thống \n";
                }
            }

            // BIDV(ok)
            $trBIDV = $table->find("tr", 5);
            for($i=1; $i<=9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trBIDV->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trBIDV->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatBIDV = DB::table('lai_suat')->insert([
                    "bank_id"=>5,
                    "bank_code"=>"bidv",
                    "bank_name"=>"BIDV",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatBIDV){
                    echo "Lấy thành công dữ liệu VietinBank \n";
                }else{
                    echo "Có lỗi xảy ra với Vietin, kiểm tra lại hệ thống \n";
                }
            }

            // MBBank(ok)
            $trMBBank = $table->find("tr", 8);
            for($i=1; $i<=9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trMBBank->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trMBBank->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatMBBank = DB::table('lai_suat')->insert([
                    "bank_id"=>15,
                    "bank_code"=>"mbbank",
                    "bank_name"=>"MBank",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatMBBank){
                    echo "Lấy thành công dữ liệu MBBank \n";
                }else{
                    echo "Có lỗi xảy ra với MBBank, kiểm tra lại hệ thống \n";
                }
            }

            //SCB(ok)
            $trSCB = $table->find("tr", 13);
            for($i=1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trSCB->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trSCB->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatSCB = DB::table('lai_suat')->insert([
                    "bank_id"=>16,
                    "bank_code"=>"scb",
                    "bank_name"=>"SCB",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>$laisuat_vnd,
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatSCB){
                    echo "Lấy thành công dữ liệu SCB \n";
                }else{
                    echo "Có lỗi xảy ra với SCB, kiểm tra lại hệ thống \n";
                }
            }

            // ArgiBank(ok)
            $trVCB = $table->find("tr", 2);
            $khongkyhan = $trVCB->find("td",1);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trVCB->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatArgi = DB::table('lai_suat')->insert([
                    "bank_id"=>13,
                    "bank_code"=>"argibank",
                    "bank_name"=>"argi",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatArgi){
                    echo "Lấy thành công dữ liệu Agribank Online \n";
                }else{
                    echo "Có lỗi xảy ra vs Argibank Online, kiểm tra lại hệ thống \n";
                }
            }

            // BacA(ok)
            $trVCB = $table->find("tr", 3);
            $khongkyhan = $trVCB->find("td",1);
            for($i = 1; $i <= 9; $i++){
                $laisuat_vnd = null;
                if(strip_tags($trVCB->find("td",$i)) == "-"){
                    $laisuat_vnd = null;
                }else{
                    $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
                }
                if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                    $kyhanslug = 0;
                }else{
                    $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
                }
                $LaiSuatBacA = DB::table('lai_suat')->insert([
                    "bank_id"=>19,
                    "bank_code"=>"baca",
                    "bank_name"=>"baca",
                    "hinhthuctietkiem"=>2,
                    "kyhan"=> $arrKyHan[$i],
                    "kyhanslug"=>$kyhanslug,
                    "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                    "moctiengui"=>null,
                    "moctienguisau"=>null,
                    "laisuattratruoc"=>null,
                    "laisuathangthang"=>null,
                    "laisuatcuoiky"=>null,
                    "laisuathangquy"=> null,
                ]);
                if($LaiSuatBacA){
                    echo "Lấy thành công dữ liệu BacA Bank \n";
                }else{
                    echo "Có lỗi xảy ra vs BacA Bank, kiểm tra lại hệ thống \n";
                }
            }
        }else{
            echo "Cannot find the data source";
        }

        // BaoViet(ok)
        $trVCB = $table->find("tr", 4);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatBaoViet = DB::table('lai_suat')->insert([
                "bank_id"=>20,
                "bank_code"=>"baoviet",
                "bank_name"=>"baoviet",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatBaoViet){
                echo "Lấy thành công dữ liệu BaoViet \n";
            }else{
                echo "Có lỗi xảy ra vs BaoViet, kiểm tra lại hệ thống \n";
            }
        }

        // Maritime(ok)
        $trVCB = $table->find("tr", 7);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatMSB = DB::table('lai_suat')->insert([
                "bank_id"=>17,
                "bank_code"=>"msb",
                "bank_name"=>"MaritimeBank",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatMSB){
                echo "Lấy thành công dữ liệu MSB \n";
            }else{
                echo "Có lỗi xảy ra vs MSB, kiểm tra lại hệ thống \n";
            }
        }

        // NamA(ok)
        $trVCB = $table->find("tr", 9);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatNamA = DB::table('lai_suat')->insert([
                "bank_id"=>21,
                "bank_code"=>"nama",
                "bank_name"=>"nama",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatNamA){
                echo "Lấy thành công dữ liệu NamA \n";
            }else{
                echo "Có lỗi xảy ra vs NamA, kiểm tra lại hệ thống \n";
            }
        }

        // NCB(ok)
        $trVCB = $table->find("tr", 10);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatNCB = DB::table('lai_suat')->insert([
                "bank_id"=>18,
                "bank_code"=>"ncb",
                "bank_name"=>"ncb",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatNCB){
                echo "Lấy thành công dữ liệu NCB \n";
            }else{
                echo "Có lỗi xảy ra vs NCB, kiểm tra lại hệ thống \n";
            }
        }

        // SHB(ok)
        $trVCB = $table->find("tr", 14);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatSHB = DB::table('lai_suat')->insert([
                "bank_id"=>3,
                "bank_code"=>"shb",
                "bank_name"=>"shb",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatSHB){
                echo "Lấy thành công dữ liệu SHB \n";
            }else{
                echo "Có lỗi xảy ra vs SHB, kiểm tra lại hệ thống \n";
            }
        }

        // VIB(ok)
        $trVCB = $table->find("tr", 15);
        $khongkyhan = $trVCB->find("td",1);
        for($i = 1; $i <= 9; $i++){
            $laisuat_vnd = null;
            if(strip_tags($trVCB->find("td",$i)) == "-"){
                $laisuat_vnd = null;
            }else{
                $laisuat_vnd = floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i)))));
            }
            if(str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))) === "khong-ky-han"){
                $kyhanslug = 0;
            }else{
                $kyhanslug = str_replace('0','',str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle($arrKyHan[$i]))));
            }
            $LaiSuatVIB = DB::table('lai_suat')->insert([
                "bank_id"=>23,
                "bank_code"=>"vib",
                "bank_name"=>"VIB",
                "hinhthuctietkiem"=>2,
                "kyhan"=> $arrKyHan[$i],
                "kyhanslug"=>$kyhanslug,
                "laisuat_vnd"=>floatval(str_replace(" ","", str_replace(",",".", strip_tags($trVCB->find("td",$i))))),
                "moctiengui"=>null,
                "moctienguisau"=>null,
                "laisuattratruoc"=>null,
                "laisuathangthang"=>null,
                "laisuatcuoiky"=>null,
                "laisuathangquy"=> null,
            ]);
            if($LaiSuatVIB){
                echo "Lấy thành công dữ liệu VIB online \n";
            }else{
                echo "Có lỗi xảy ra vs VIB, kiểm tra lại hệ thống \n";
            }
        }
    }

    public function shb($SimpleHTMLDOM){
        $url = 'https://www.shb.com.vn/category/lien-ket-nhanh/lai-suat-tiet-kiem/';
        $htmlMB = $SimpleHTMLDOM->file_get_html($url);
        $html_tb = $htmlMB->find('div.detail table tbody tr',0);
        $html_tr = $html_tb->find('td table tbody tr');
        $result = array();
        $unset = [0,1,2,4,5,6,10,11,13,14,16,17,19];
        foreach ($unset as $un){
            unset($html_tr[$un]);
        }
        $lx = [0,1,2,3,6,9,12,18,24,36];
        $value = ['<2','>2','tt','ht'];
        $i = 0;
        foreach ($html_tr as $key => $tr){
            $td = $tr->find('td');
            unset($td[0]);
            $v = 0;
            foreach ($td as $t){
                $p = $t->find('p',0);
                if ($p){
                    $result[$lx[$i]][$value[$v]] = $p->innertext;
                }else{
                    $result[$lx[$i]][$value[$v]] = $t->innertext;
                }
                $v++;
            }
            $i++;
        }
        foreach($result as $key=>$value){
            $LaiSuat = new LaiSuat();

            $LaiSuat->bank_id = 3;
            $LaiSuat->bank_code = 'shb';
            $LaiSuat->bank_name = 'SHB';
            $LaiSuat->hinhthuctietkiem = 1;
            $LaiSuat->kyhan = $key;
            $LaiSuat->kyhanslug = $key;
            $LaiSuat->moctiengui = "Dưới 2 tỷ";
            $LaiSuat->moctienguisau = null;
            $LaiSuat->laisuat_vnd = $value["<2"];
            $LaiSuat->laisuat_eur = null;
            $LaiSuat->laisuat_usd = null;
            try{
                $LaiSuat->save();
                echo "Cập nhật lãi suất mốc > 2 tỷ ngân hàng SHB thành công. \n";
            }catch(\Exception $e){
                echo $e;
            }
        }

        foreach($result as $key=>$value){
            $LaiSuat = new LaiSuat();

            $LaiSuat->bank_id = 3;
            $LaiSuat->bank_code = 'shb';
            $LaiSuat->bank_name = 'SHB';
            $LaiSuat->hinhthuctietkiem = 1;
            $LaiSuat->kyhan = $key;
            $LaiSuat->kyhanslug = $key;
            $LaiSuat->moctiengui = "Trên 2 tỷ";
            $LaiSuat->moctienguisau = null;
            $LaiSuat->laisuat_vnd = $value[">2"];
            $LaiSuat->laisuat_eur = null;
            $LaiSuat->laisuat_usd = null;
            try{
                $LaiSuat->save();
                echo "Cập nhật lãi suất mốc < 2 tỷ ngân hàng SHB thành công. \n";
            }catch(\Exception $e){
                echo $e;
            }
        }
    }


    public function ocb($SimpleHTMLDOM){

        $url = "https://www.ocb.com.vn/vi/lai-suat.html";

        try{
            $html = $SimpleHTMLDOM->file_get_html($url);
        }catch (\Exception $exception){
            $html = null;
        }

        if($html){
            $changeTitle = new ChangeText();
            $table = $html->find("#tbVND", 0);

            for($i = 1; $i <= 12; $i++){
                $tr = $table->find("tr", $i);
                $kyhan = html_entity_decode(strip_tags($tr->find("td", 0)));
                $kyhanslug = html_entity_decode(str_replace("-thang",'',str_replace('0','',html_entity_decode($changeTitle->changeTitle(strip_tags($kyhan))))));

                if($kyhanslug === "khong-ky-han"){
                    $kyhanslug = 0;
                }
                $laisuat = str_replace(' ','',str_replace('(***)','',str_replace('(**)','',str_replace('(*)','',strip_tags($tr->find("td", 1))))));
                $laisuatOnline = strip_tags($tr->find("td",2));

                if($laisuat == null || $laisuat == ""){
                    $laisuat = null;
                }
                if($laisuatOnline == null || $laisuat == ""){
                    $laisuatOnline = null;
                }
                $insertOCB = DB::table('lai_suat')->insert([
                    'bank_id'=>22,
                    'bank_code'=>'ocb',
                    'bank_name'=>'ocb',
                    'hinhthuctietkiem'=>1,
                    'kyhan'=>html_entity_decode($kyhan),
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>floatval($laisuat)
                ]);
                $insertOCBOnline = DB::table('lai_suat')->insert([
                    'bank_id'=>22,
                    'bank_code'=>'ocb',
                    'bank_name'=>'ocb',
                    'hinhthuctietkiem'=>2,
                    'kyhan'=>html_entity_decode($kyhan),
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>floatval($laisuatOnline)
                ]);
                if($insertOCB){
                    echo "Cập nhật thành công lãi suất ngân hàng OCB với kỳ hạn(".$kyhan. ") \n";
                }else{
                    echo "Cập nhật không thành công lãi suất ngân hàng OCB với kỳ hạn(".$kyhan. ") \n";
                }
                if($insertOCBOnline){
                    echo "Cập nhật thành công lãi suất Online ngân hàng OCB với kỳ hạn(".$kyhan. ") \n";
                }else{
                    echo "Cập nhật không thành công lãi suất Online ngân hàng OCB với kỳ hạn(".$kyhan. ") \n";
                }
            }
        }else{
            echo "Không tìm được dữ liệu trong đường dẫn này! \n";
        }
    }

    public function vib($SimpleHTMLDOM){
//        $url = "https://vib.com.vn/wps/portal/vn/product-landing/tai-khoan-ngan-hang?gclid=Cj0KCQjwov3nBRDFARIsANgsdoFO1y1R6481M5UVeP_vwI6_U-VgaXSvTTrFK7CASkALM8-dp5OzZ5kaAowfEALw_wcB";
        $url = "https://webgia.com/lai-suat/vib/";

        try{
            $html = $SimpleHTMLDOM->file_get_html($url);
        }catch(\Exception $exception){
            $html = null;
        }

        if($html){

            $changeText = new ChangeText();
            $table = $html->find(".table-responsive",0);

            for($i = 1; $i < 20; $i++){

                $tr = $table->find("tr",$i);
                $kyhan = strip_tags($tr->find("td",0));
                $kyhanslug = str_replace('-thang','',str_replace('-ngay','',$changeText->changeTitle($kyhan)));
                if($kyhanslug === "khong-ky-han"){
                    $kyhanslug = 0;
                }
                $laisuat = str_replace(',','.',str_replace('%','',strip_tags($tr->find("td", 1))));
                $insertVIB = DB::table('lai_suat')->insert([
                    'bank_id'=>23,
                    'hinhthuctietkiem'=>1,
                    'bank_code'=>'vib',
                    'bank_name'=>'vib',
                    'kyhan'=>html_entity_decode($kyhan),
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>floatval($laisuat)
                ]);
                if($insertVIB){
                    echo "Cập nhật thông tin lãi suất ngân hàng VIB với mốc gửi là: " . $kyhan . " thành công \n ";
                }else{
                    echo "Cập nhật thông tin lãi suất ngân hàng VIB với mốc gửi là: " . $kyhan . " không thành công  \n";
                }
            }

        }else{
            echo "Không tìm được dữ liệu trong đường dẫn này! \n";
        }

    }


    public function baoviet($SimpleHTMLDOM){
        $url = "https://www.baovietbank.vn/vi/lai-suat-tiet-kiem-vnd";

        try{
            $html = $SimpleHTMLDOM->file_get_html($url);
        }catch(\Exception $exception){
            $html = null;
        }

        if($html){
            $changeText = new ChangeText();
            $table = $html->find(".view-content table", 0);

            for($i = 1; $i <= 21; $i++){

                $tr = $table->find("tr", $i);
                $kyhan = str_replace('&nbsp;','',html_entity_decode(strip_tags($tr->find("td", 1))));
                $kyhanslug = str_replace('-thang','',$this->changeKyhanSlug($changeText->changeTitle(strip_tags($kyhan))));
                $laisuat = floatval(str_replace(' ','',html_entity_decode(strip_tags($tr->find("td", 2)))));
                if($kyhanslug === "kkh"){
                    $kyhanslug = 0;
                }
                if($laisuat == null || $laisuat == 0 || $laisuat == "-"){
                    $laisuat = null;
                }
                if($kyhanslug == null || $kyhanslug == ""){
                    continue;
                }
                $insertBaoViet = DB::table('lai_suat')->insert([
                    'bank_id'=>20,
                    'bank_code'=>'baoviet',
                    'bank_name'=>'BaovietBank',
                    'hinhthuctietkiem'=>1,
                    'kyhan'=>html_entity_decode($kyhan),
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>$laisuat
                ]);
                if($insertBaoViet){
                    echo "Cập nhật lãi suất theo kỳ hạn(". $kyhan . ") của ngân hàng Bảo Việt thành công <hr/>";
                }else{
                    echo "Cập nhật lãi suất theo kỳ hạn(". $kyhan . ") của ngân hàng Bảo Việt không thành công <hr/>";
                }

            }
        }else{
            echo "Không tìm được dữ liệu trong đường dẫn này!";
        }
    }

    public function laisuatnongnghiep($SimpleHTMLDOM){
        try {
            $html = $SimpleHTMLDOM->file_get_html('http://agribank.com.vn/LayOut/Pages/LaiSuatDetail.aspx');
        } catch (\Exception $e) {
            $html = null;
        }
        if ($html) {
            $tt1 = $html->find("tr td span");
            $thongtin1 = array();
            foreach ($tt1 as $key => $t1) {
                $thongtin1[$key] = $t1->innertext;
            }

            $tt2 = $html->find("tr td[style^='color']");
            $thongtin2 = array();
            foreach ($tt2 as $key => $t2) {
                $thongtin2[$key] = $t2->innertext;
            }

            $agri = array_chunk($thongtin1,4);

            $interest = array();
            foreach ($agri as $key => $agr){

               array_push($agr, $thongtin2[$key]);

                $interest[$key] = $agr;
            }

            foreach($interest as $value){
                $changeText = new ChangeText();
                $mangoaite = $value[0];
                if($mangoaite === "USD" || $mangoaite === "EUR"){
                    continue;
                }
                $hinhthuctietkiem = 1;
                $moctiengui = $value[3];
                $kyhan = $value[2];
                $kyhanslug = str_replace('-thang','',$changeText->changeTitle($value[2]));
                if($kyhanslug === "khong-ky-han"){
                    $kyhanslug = 0;
                }
                $valInterest = str_replace('%','', str_replace(' ','',$value[4]));
                $insertAgribank = DB::table('lai_suat')->insert([
                    'bank_id'=>13,
                    'bank_code'=>'agribank',
                    'bank_name'=>'ArgiBank',
                    'hinhthuctietkiem'=>1,
                    'moctiengui'=>$moctiengui,
                    'kyhan'=>$kyhan,
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>$valInterest
                ]);
                if($insertAgribank){
                    echo "Cập nhật thành công lãi suất ngân hàng Argibank: " . $kyhan . "\n";
                }else{
                    echo "Cập nhật lãi suất ngân hàng Argibank: " . $kyhan . "\n";
                }
            }
        }
    }

    public function ncb($htmlDOM){
        $url = "https://webgia.com/lai-suat/ncb/";
        $changeText = new ChangeText();
        try{
            $html = $htmlDOM->file_get_html($url);
        }catch(\Exception $e){
            $html = null;
        }
        if($html){
            $table = $html->find(".table-responsive .table",0);
            $countTR = $table->find("tr");
            for($i = 2; $i < count($countTR); $i++){
                $tr = $table->find("tr",$i);
                $kyhan = strip_tags($tr->find('td',0));
                $kyhanslug = str_replace('-ngay','',str_replace('-thang','',$changeText->changeTitle(strip_tags($kyhan))));
                if($kyhanslug === "khong-ky-han"){
                    $kyhanslug = 0;
                }
                $laisuat_vnd = str_replace('%','',str_replace(',','.',strip_tags($tr->find("td",1))));
                if($laisuat_vnd == "-" || $laisuat_vnd == ""){
                    $laisuat_vnd = null;
                }
                $insertNCB = DB::table('lai_suat')->insert([
                    'bank_id'=>18,
                    'kyhan'=>$kyhan,
                    'kyhanslug'=>$kyhanslug,
                    'laisuat_vnd'=>$laisuat_vnd,
                    'bank_code'=>'ncb',
                    'bank_name'=>'NCB',
                    'hinhthuctietkiem'=>1
                ]);
                if($insertNCB){
                    echo "Cap nhat lai suat ngan hang NCB thanh cong \n";
                }else{
                    echo "Cap nhat lai suat ngan hang NCB khong thanh cong \n";
                }
            }
        }else{
            echo " cannot find data";
        }
    }


    // change slug code
    protected function changeKyhanSlug($str){
        if($str == "kkh" || $str == "khong-ky-han-" || $str == "khong-ky-han"){
            return "kkh";
        }
        if($str == "1-tuan" || $str == "1-tuan-"){
            return "1-tuan";
        }
        if($str == "2-tuan" || $str == "2-tuan-"){
            return "2-tuan";
        }
        if($str == "3-tuan" || $str == "3-tuan-"){
            return "3-tuan";
        }
        if($str == "01-tuan"){
            return "1-tuan";
        }
        if($str == "02-tuan"){
            return "2-tuan";
        }
        if($str == "03-tuan"){
            return "3-tuan";
        }
        if($str == "1-th-aacute-ng" || $str == "1-thang-"){
            return "1-thang";
        }
        if($str == "2-th-aacute-ng" || $str == "2-thang-"){
            return "2-thang";
        }
        if($str == "3-th-aacute-ng" || $str == "3-thang-"){
            return "3-thang";
        }
        if($str == "4-th-aacute-ng" || $str == "4-thang-"){
            return "4-thang";
        }
        if($str == "5-th-aacute-ng" || $str == "5-thang-"){
            return "5-thang";
        }
        if($str == "6-th-aacute-ng" || $str == "6-thang-"){
            return "6-thang";
        }
        if($str == "7-th-aacute-ng" || $str == "7-thang-"){
            return "7-thang";
        }
        if($str == "8-th-aacute-ng" || $str == "8-thang-"){
            return "8-thang";
        }
        if($str == "9-th-aacute-ng" || $str == "9-thang-"){
            return "9-thang";
        }
        if($str == "07-th-aacute-ng" || $str == "07-thang-"){
            return "7-thang";
        }
        if($str == "08-th-aacute-ng" || $str == "08-thang-"){
            return "8-thang";
        }
        if($str == "09-th-aacute-ng" || $str == "09-thang-"){
            return "9-thang";
        }
        if($str == "10-th-aacute-ng" || $str == "10-thang-"){
            return "10-thang";
        }
        if($str == "11-th-aacute-ng" || $str == "11-thang-"){
            return "11-thang";
        }
        if($str == "12-th-aacute-ng" || $str == "12-thang-"){
            return "12-thang";
        }
        if($str == "13-th-aacute-ng" || $str == "13-thang-"){
            return "13-thang";
        }
        if($str == "14-th-aacute-ng" || $str == "14-thang-"){
            return "14-thang";
        }
        if($str == "15-th-aacute-ng" || $str == "15-thang-"){
            return "15-thang";
        }
        if($str == "16-th-aacute-ng" || $str == "16-thang-"){
            return "16-thang";
        }
        if($str == "17-th-aacute-ng" || $str == "17-thang-"){
            return "17-thang";
        }
        if($str == "18-th-aacute-ng" || $str == "18-thang-"){
            return "18-thang";
        }
        if($str == "19-th-aacute-ng" || $str == "19-thang-"){
            return "19-thang";
        }
        if($str == "20-th-aacute-ng" || $str == "20-thang-"){
            return "20-thang";
        }
        if($str == "21-th-aacute-ng" || $str == "21-thang-"){
            return "21-thang";
        }
        if($str == "22-th-aacute-ng" || $str == "22-thang-"){
            return "22-thang";
        }
        if($str == "23-th-aacute-ng" || $str == "23-thang-"){
            return "23-thang";
        }
        if($str == "24-th-aacute-ng" || $str == "24-thang-"){
            return "24-thang";
        }
        if($str == "25-th-aacute-ng" || $str == "25-thang-"){
            return "25-thang";
        }
        if($str == "26-th-aacute-ng" || $str == "26-thang-"){
            return "26-thang";
        }
        if($str == "27-th-aacute-ng" || $str == "27-thang-"){
            return "27-thang";
        }
        if($str == "28-th-aacute-ng" || $str == "28-thang-"){
            return "28-thang";
        }
        if($str == "29-th-aacute-ng" || $str == "29-thang-"){
            return "29-thang";
        }
        if($str == "30-th-aacute-ng" || $str == "30-thang-"){
            return "30-thang";
        }
        if($str == "31-th-aacute-ng" || $str == "31-thang-"){
            return "31-thang";
        }
        if($str == "32-th-aacute-ng" || $str == "32-thang-"){
            return "32-thang";
        }
        if($str == "33-th-aacute-ng" || $str == "33-thang-"){
            return "33-thang";
        }
        if($str == "34-th-aacute-ng" || $str == "34-thang-"){
            return "34-thang";
        }
        if($str == "35-th-aacute-ng" || $str == "35-thang-"){
            return "35-thang";
        }
        if($str == "36-th-aacute-ng" || $str == "36-thang-"){
            return "36-thang";
        }
    }


}
