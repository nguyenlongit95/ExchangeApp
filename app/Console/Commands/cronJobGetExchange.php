<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Carbon\Carbon;
use App\Helpers\SimpleHtmlDom;
use App\Models\NgoaiTe;
use App\Models\CurrencyCode;
use App\Models\NgoaiTeCron;

class cronJobGetExchange extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'cronJob:getExchanges';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call to bank and get data currency';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    var $AccessKey = '49fdc5f4-b909-4a17-ad1b-99945aa2af67';
    var $SecretAccessKey = '5459f4e06d3cf8be95659ef2a5f57d65846775b5';
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * function constructor using call to Bank
     * Call to function get data currency bank
     * @call protected function
     */
    public function handle()
    {
        $SimpleHTMLDOM = new SimpleHtmlDom();
        $Carbon = Carbon::now();

        $NgoaiTeCron = $this->NewCron();

        if ($NgoaiTeCron == null) {
            $NgoaiTeCron = 1;
        }
        //        //Brgin call function
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://www.acb.com.vn/ACBComponent/jsp/html/vn/exchange/getlisttygia.jsp");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "txtngaynt=01/04/2019&lannt=1");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            $server_output = null;
        }
        if ($server_output) {
            $html = $SimpleHTMLDOM->str_get_html($server_output);
            $this->abc($SimpleHTMLDOM, $NgoaiTeCron, $html);
        }

        $this->BIDV($NgoaiTeCron);

        try {
            $urlTechcombank = "https://www.techcombank.com.vn/cong-cu-tien-ich/ti-gia/ti-gia-hoi-doai";
            $htmlTechcombank = $SimpleHTMLDOM->file_get_html($urlTechcombank);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công BIDV vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "bidv");
            $htmlTechcombank = null;
        }
        $this->Techcombank($htmlTechcombank, $NgoaiTeCron);
        try {
            $urlHSBC = "https://www.hsbc.com.vn/1/2/miscellaneous/exchange_rate";
            $htmlHSBC = $SimpleHTMLDOM->file_get_html($urlHSBC);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công HSBC Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "hsbc");
            $htmlHSBC = null;
        }
        $this->HSBC($htmlHSBC, $NgoaiTeCron);
        try {
            $urlSHB = "https://www.shb.com.vn/tygia/ty-gia-hoi-doai/";
            $htmlSHB = $SimpleHTMLDOM->file_get_html($urlSHB);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công SHB Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "shb");
            $htmlSHB = null;
        }
        $this->SHB($htmlSHB, $NgoaiTeCron);
        $urlViettinBank = 'https://www.vietinbank.vn/web/home/vn/ty-gia/';
        try {
            $htmlViettinBank = $SimpleHTMLDOM->file_get_html($urlViettinBank);
            $this->vietinbank($htmlViettinBank, $NgoaiTeCron);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công Vietin Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "vietin");
            $html = null;
        }
        try {
            $urlSacombank = 'https://www.sacombank.com.vn/company/Pages/ty-gia.aspx';
            $htmlSacombank = $SimpleHTMLDOM->file_get_html($urlSacombank);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công Sacombank Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "sacombank");
            $htmlSacombank = null;
        }
        $this->sacombank($htmlSacombank, $NgoaiTeCron);
        try {
            $urlVietComBank = 'http://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx';
            $htmlVietcomBank = file_get_contents($urlVietComBank);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công Vietcom Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "vietcom");
            $htmlVietcomBank = null;
        }
        $this->vietcombank($htmlVietcomBank, $NgoaiTeCron);
        try {
            $urlDongABank = "http://kinhdoanh.dongabank.com.vn/widget/temp?p_p_id=DTSCDongaBankEView_WAR_DTSCDongaBankIERateportlet&p_p_lifecycle=0&p_p_state=maximized&p_p_mode=view";
            $htmlDongABank = $SimpleHTMLDOM->file_get_html($urlDongABank);
        } catch (\Exception $e) {
            $this->CreateLog($NgoaiTeCron, "Cập nhật không thành công Đông Á Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "dab");
            $htmlDongABank = null;
        }
        $this->dongabank($htmlDongABank, $NgoaiTeCron);

        $url = "https://eximbank.com.vn/WebsiteExrate/ExchangeRate_vn_2012.aspx";
        try {
            $html = $SimpleHTMLDOM->file_get_html($url);
        } catch (\Exception $exception) {
            $html = null;
        }
        $this->eximbank($html, $NgoaiTeCron);

        $Carbon = new Carbon();
        $day = $Carbon->day;
        $month = $Carbon->month;
        $year = $Carbon->year;
        $date = $Carbon->format('d/m/Y');
        $time = $Carbon->format('h:m:i');
        $url = "http://www.agribank.com.vn/LayOut/Pages/TyGiaPopUp.aspx?date=" . $day . "/" . $month . "/" . $year . "&lang=1";
        //        $url = "http://www.agribank.com.vn/LayOut/Pages/TyGiaPopUp.aspx?date=8/4/2019&lang=1";
        try {
            $html = $SimpleHTMLDOM->file_get_html($url);
        } catch (\Exception $exception) {
            $html = null;
        }
        if ($this->agribank($html, $NgoaiTeCron) === "datanone") {
            $day = $day - 1;
        } else { }

        $urlMBBank = "https://webgia.com/ty-gia/mbbank/";
        try {
            $htmlMBBank = $SimpleHTMLDOM->file_get_html($urlMBBank);
        } catch (\Exception $exception) {
            $htmlMBBank = null;
        }
        $this->mbbank($htmlMBBank, $NgoaiTeCron);

        $urlTPBank = "https://webgia.com/ty-gia/tpbank/";

        try {
            $htmlTPBank = $SimpleHTMLDOM->file_get_html($urlTPBank);
        } catch (\Exception $exception) {
            $htmlTPBank = null;
        }
        $this->tpBank($htmlTPBank, $NgoaiTeCron);

        $this->scb($SimpleHTMLDOM, $NgoaiTeCron);

        $urlMSB = "https://maritime.ngan-hang.com/";
        try {
            $htmlMSB = $SimpleHTMLDOM->file_get_html($urlMSB);
        } catch (Exception $exception) {
            $htmlMSB = null;
        }
        $this->msb($htmlMSB, $NgoaiTeCron);

        $this->insertNameCurrency();

        $CapNhatNgoaiTe = new ApiFrontController();
        $CapNhatNgoaiTe->capnhatngoaite();
    }
    /**
     * Add cron
     * Mỗi lần cron dữ liệu sẽ thêm 1 bản ghi vào cron
     * */
    protected function NewCron()
    {
        $NgoaiTeCron = new NgoaiTeCron();
        $Carbon = Carbon::now();
        $NgoaiTeCron->cronkey = str_random(5) . "_" . $Carbon->toDateString();
        if ($NgoaiTeCron->save()) {
            return $NgoaiTeCron->id;
        } else {
            return null;
        }
    }
    public function CreateLog($NgoaiTeCron, $Log, $bank)
    {
        $NgoaiTeLog = DB::table('ngoai_te_logs')->insert(
            [
                'id_cron' => $NgoaiTeCron,
                'logs' => $Log,
                'bank' => $bank
            ]
        );
        if ($NgoaiTeLog) {
            return "Ok";
        }
        return "nope";
    }
    /**
     * more function get data bank
     * @ More function change data
     * Insert to database
     * */

    /**
     * function ACB
     * */
    protected function abc($SimpleHTMLDOM, $NgoaiTeCron, $html)
    {
        if ($html) {
            $rows = $html->find(".wrap-content-search-big tr");

            for ($i = 1; $i < count($rows) - 1; $i++) {

                $Currency_name = str_replace(' ', '', str_replace("\t", "", strip_tags($rows[$i]->find('td', 1))));

                if ($Currency_name == "USD(1,2)" || $Currency_name == "USD(5,10,20)") { } else {

                    if ($Currency_name == "USD(50,100)") {
                        $Currency_name = "USD";
                    } else { }
                    $MuaTienMat = str_replace(',', '', strip_tags($rows[$i]->find('td', 2)));
                    $MuaChuyenKhoan = str_replace(',', '', strip_tags($rows[$i]->find('td', 3)));
                    $BanTienmat = str_replace(',', '', strip_tags($rows[$i]->find('td', 4)));
                    $BanChuyenKhoan = str_replace(',', '', strip_tags($rows[$i]->find('td', 5)));

                    $Carbon = Carbon::now();

                    $NgoaiTe = new NgoaiTe();
                    $NgoaiTe->code = str_replace(' ', '', $Currency_name);
                    $NgoaiTe->bank_id = 12;
                    $NgoaiTe->bank_code = "acb";
                    $NgoaiTe->bank_name = "acb";
                    $NgoaiTe->bank_image = "/storage/userfiles/images/icons/acb.png";
                    $NgoaiTe->symbol = $this->checkSymbol(str_replace(' ', '', $Currency_name));
                    $NgoaiTe->image = "/storage/currency/" . str_replace(' ', '', $Currency_name) . ".png";
                    $NgoaiTe->cron_id = $NgoaiTeCron;
                    $NgoaiTe->vname =  str_replace(' ', '', $Currency_name);
                    if ($MuaTienMat == null || $MuaTienMat == "") {
                        $NgoaiTe->muatienmat = 0;
                    } else {
                        $NgoaiTe->muatienmat = $MuaTienMat;
                    }
                    if ($MuaChuyenKhoan == null || $MuaChuyenKhoan == "") {
                        $NgoaiTe->muachuyenkhoan = 0;
                    } else {
                        $NgoaiTe->muachuyenkhoan = $MuaChuyenKhoan;
                    }
                    if ($BanTienmat == null || $BanTienmat == "") {
                        $NgoaiTe->bantienmat = 0;
                    } else {
                        $NgoaiTe->bantienmat = $BanTienmat;
                    }
                    if ($BanChuyenKhoan == null || $BanChuyenKhoan == "") {
                        $NgoaiTe->banchuyenkhoan = 0;
                    } else {
                        $NgoaiTe->banchuyenkhoan = $BanChuyenKhoan;
                    }
                    $NgoaiTe->date = $Carbon;
                    $NgoaiTe->time = $Carbon;
                    $arr_diff = $this->tygiaNow($Currency_name, 12, $NgoaiTe, $NgoaiTeCron);
                    if ($arr_diff) {
                        $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Cập nhật dữ liệu ngân hàng ACB với đồng " . $Currency_name . " thành công \n";
                    } else { // failed
                        echo "Insert new money \n";
                        $this->CreateLog($NgoaiTeCron, "Cập nhật ACB không thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "acb");
                        continue;
                    }
                }
            }
            $this->CreateLog($NgoaiTeCron, "Cập nhật thành công ACB vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "acb");
        }
    }

    /**
     * function BIDV
     * */
    protected function BIDV($NgoaiTeCron)
    {
        $url = "https://www.bidv.com.vn/ServicesBIDV/ExchangeDetailServlet";
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            $result = null;
        }
        $Carbon = Carbon::now();
        if ($result) {
            $response = json_decode($result);
            $responseData = $response->data;
            foreach ($responseData as $data) {
                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->code = $data->currency;
                if ($data->currency === "USD(1-2-5)" || $data->currency === "USD(10-20)") {
                    continue;
                }
                $NgoaiTe->bank_id = 5;
                $NgoaiTe->bank_code = "bidv";
                $NgoaiTe->bank_name = "BIDV";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/BIDV.png";
                $NgoaiTe->symbol = $this->checkSymbol(str_replace(' ', '', $data->currency));
                $NgoaiTe->image = "/storage/currency/" . str_replace(' ', '', $data->currency) . ".png";
                $NgoaiTe->vname = $data->nameVI;
                $NgoaiTe->ename = $data->nameEN;
                if ($data->muaTm == null || $data->muaTm == "") {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval(str_replace(',', '', $data->muaTm));
                }
                if ($data->ban == null || $data->ban == "") {
                    $NgoaiTe->bantienmat = 0;
                    $NgoaiTe->banchuyenkhoan = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval(str_replace(',', '', $data->ban));
                    $NgoaiTe->banchuyenkhoan = floatval(str_replace(',', '', $data->ban));
                }
                if ($data->muaCk == null || $data->muaCk == "") {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval(str_replace(',', '', $data->muaCk));
                }
                $NgoaiTe->date = $Carbon;
                $NgoaiTe->time = $response->hour;
                //Save vào bảng Ngoại Tệ mới
                $arr_diff = $this->tygiaNow($data->currency, 5, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) {
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu BIDV với đồng " . $data->currency . " thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    $this->CreateLog($NgoaiTeCron, "Cập nhật BIDV không thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "bidv");
                    continue;
                }
            }
            $this->CreateLog($NgoaiTeCron, "Cập nhật thành công BIDV vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "bidv");
        }
    }
    /**
     * Tách chuỗi để lấy thời gian
     * Tách chuỗi để lấy ngày tháng
     * */
    protected function getTimeCurrency($str)
    {
        $subStr = substr($str, 20);
        while ($subStr) {
            $subStrDate = substr($subStr, 11);
            return $subStrDate;
        }
    }
    protected function getDateCurrency($str)
    {
        $subStr = substr($str, 20);
        $tok = strtok($subStr, " ");
        $Date = str_replace('/', '-', $tok);
        $Carbon = new Carbon($Date);
        return $Carbon->year . "-" . $Carbon->month . "-" . $Carbon->day;
    }
    /**
     * function get Data Techcombank
     *
     * @return array list
     */
    protected function Techcombank($htmlTechcombank, $NgoaiTeCron)
    {
        if ($htmlTechcombank) {
            $rows = $htmlTechcombank->find("div#exchangeFilterContainer div.table-responsive table tbody tr");
            $time = $htmlTechcombank->find("div#exchangeFilterContainer div.table-responsive table tbody td em");
            $strip_time = strip_tags($time[0]->innertext);
            /**
             * Begin for init i = 4, i + 2
             * Begin for init j = 5, j + 2
             * */
            $this->getDate($strip_time);
            $arrayDataTechComBank = array();
            if (count($rows) > 0) {
                for ($i = 4; $i < count($rows) - 17; $i += 2) {
                    for ($j = 5; $j < count($rows) - 17; $j += 2) {
                        if ($i = $j + 1) {
                            $checkCurrency = strip_tags($rows[$i]->find("td", 0)->innertext);
                            if ($checkCurrency === "USD, (5,10,20)") {
                                continue;
                            } else {
                                $currency_code = strip_tags($rows[$i]->find("td", 0)->innertext);
                                if ($checkCurrency == "USD,50-100") {
                                    $currency_code = "USD";
                                }
                                $currency_name = $rows[$j]->find('td', 0)->innertext;
                                $currency_buy_money = strip_tags($this->changenbsp($rows[$i]->find('td', 1)));
                                $currency_transfer = strip_tags($this->changenbsp($rows[$i]->find('td', 2)->innertext));
                                $currency_sell = strip_tags($this->changenbsp($rows[$i]->find('td', 3)));
                                $arrTemp = array(
                                    "currency_code" => strip_tags($currency_code),
                                    "currency_name" => $currency_name,
                                    "currency_buy_money" => $currency_buy_money,
                                    "currency_transfer" => $currency_transfer,
                                    "currency_sell" => $currency_sell,
                                    "date" => $this->getDate($strip_time),
                                    "time" => $this->getDate($strip_time)
                                );
                            }
                        } else {
                            echo $i . "no ";
                        }
                        array_push($arrayDataTechComBank, $arrTemp);
                    }
                }
                if (count($arrayDataTechComBank) > 0) {
                    foreach ($arrayDataTechComBank as $data) {
                        $NgoaiTe = new NgoaiTe();
                        $NgoaiTe->code = $data["currency_code"];
                        $NgoaiTe->bank_id = 1;
                        $NgoaiTe->bank_code = "techcom";
                        $NgoaiTe->bank_name = "TechcomBank";
                        $NgoaiTe->bank_image = "/storage/userfiles/images/icons/TCB.png";
                        $NgoaiTe->symbol = $this->checkSymbol(str_replace(' ', '', $data["currency_code"]));
                        $NgoaiTe->image = "/storage/currency/" . str_replace(' ', '', $data["currency_code"]) . ".png";
                        $NgoaiTe->cron_id = $NgoaiTeCron;
                        $NgoaiTe->vname = $data["currency_name"];
                        $NgoaiTe->ename = $data["currency_code"];
                        if ($data["currency_buy_money"] == null || $data["currency_buy_money"] == "") {
                            $NgoaiTe->muatienmat = 0;
                        } else {
                            $NgoaiTe->muatienmat = floatval(str_replace(',', '', $data["currency_buy_money"]));
                        }
                        if ($data["currency_sell"] == null || $data["currency_sell"] == "") {
                            $NgoaiTe->bantienmat = 0;
                        } else {
                            $NgoaiTe->bantienmat = floatval(str_replace(',', '', $data["currency_sell"]));
                        }
                        if ($data["currency_transfer"] == null || $data["currency_transfer"] == "") {
                            $NgoaiTe->muachuyenkhoan = 0;
                        } else {
                            $NgoaiTe->muachuyenkhoan = floatval(str_replace(',', '', $data["currency_transfer"]));
                        }
                        if ($data["currency_transfer"] == null || $data["currency_transfer"] == "") {
                            $NgoaiTe->banchuyenkhoan = 0;
                        } else {
                            $NgoaiTe->banchuyenkhoan = floatval(str_replace(',', '', $data["currency_transfer"]));
                        }
                        $NgoaiTe->date = $data["date"];
                        $NgoaiTe->time = $data["time"];
                        //Save vào bảng Ngoại Tệ mới
                        $arr_diff = $this->tygiaNow($data["currency_code"], 1, $NgoaiTe, $NgoaiTeCron);
                        if ($arr_diff) {
                            $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                            $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                            $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                            $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                            $NgoaiTe->save();
                            echo "Cập nhật dữ liệu Techcombank với đồng " . $data['currency_code'] . " thành công \n";
                        } else { // failed
                            echo "Insert new money \n";
                            continue;
                        }
                    }
                    $Carbon = Carbon::now();
                    $this->CreateLog($NgoaiTeCron, "Cập nhật thành công Techcombank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "techcom");
                    return response()->json(["message", "Get data success"]);
                } else {
                    return response()->json(["message", "Cannot find data in url!"]);
                }
            } else { }
        }
    }
    /**
     * More function Techcombank
     * */
    protected function getDate($str)
    {
        $arrTime = explode(' ', $str);
        $Date = str_replace('&nbsp;', '', $arrTime[7]);
        $Carbon = Carbon::now();
        return $Carbon;
    }
    protected function changenbsp($str)
    {
        return str_replace('&nbsp;', '', $str);
    }
    /**
     * End more function Techcombank
     * */
    /**
     * HSBC function
     * */
    protected function HSBC($html, $NgoaiTeCron)
    {
        if ($html) {
            $rows = $html->find("table.hsbcTableStyleForRates02 tr.hsbcTableRow03");
            $getTime = $html->find("table.hsbcTableStyleForRates02 td.ForTime01", 0)->innertext;
            $time = $this->changeCharaterbsp($getTime);
            $arrDataHSBC = array();
            if (count($rows) > 0) {
                for ($i = 0; $i < count($rows); $i++) {
                    $currency_name = $rows[$i]->find("td.ForRatesColumn02", 0)->innertext;
                    $currency_buy_money = $rows[$i]->find('td.ForRatesColumn02', 1)->innertext;
                    $currency_transfer = $rows[$i]->find('td.ForRatesColumn02', 2)->innertext;
                    $currency_sell_money = $rows[$i]->find('td.ForRatesColumn02', 3)->innertext;
                    $currency_sell_tranfer = $rows[$i]->find('td.ForRatesColumn02', 4)->innertext;
                    $arrTemp = array(
                        "currency_code" => $this->getCurrencyCodeHSBC($currency_name),
                        "currency_name" => $this->changeCharaterbsp($currency_name),
                        "currency_buy_money" => $currency_buy_money,
                        "currency_buy_transfer" => $currency_transfer,
                        "currency_sell_money" => $currency_sell_money,
                        "currency_sell_tranfer" => $currency_sell_tranfer,
                        "date" => $this->getDateHSBC($time),
                        "time" => $this->getTimeHSBC($time)
                    );
                    array_push($arrDataHSBC, $arrTemp);
                }
            } else {
                return response()->json("cannot get data");
            }
            if (count($arrDataHSBC) > 0) {
                foreach ($arrDataHSBC as $data) {
                    $NgoaiTe = new NgoaiTe();
                    $code = str_replace('(', '', $data['currency_code']);
                    $code1 = str_replace(')', '', $code);
                    $NgoaiTe->code = $code1;
                    $NgoaiTe->bank_id = 2;
                    $NgoaiTe->bank_code = "hsbc";
                    $NgoaiTe->bank_name = "HSBC";
                    $NgoaiTe->bank_image = "/storage/userfiles/images/icons/HSBC.png";
                    $NgoaiTe->symbol = $this->checkSymbol(str_replace(' ', '', str_replace('(', '', $data['currency_code'])));
                    $NgoaiTe->image = "/storage/currency/" . str_replace(' ', '', str_replace('(', '', $data['currency_code'])) . ".png";
                    $NgoaiTe->cron_id = $NgoaiTeCron;
                    $NgoaiTe->vname = "";
                    $NgoaiTe->ename = "";
                    if ($data["currency_buy_money"] == null || $data["currency_buy_money"] == "") {
                        $NgoaiTe->muatienmat = 0;
                    } else {
                        $NgoaiTe->muatienmat = floatval(str_replace(',', '.', str_replace('.', '', $data['currency_buy_money'])));
                    }
                    if ($data["currency_sell_money"] == null || $data["currency_sell_money"] == "") {
                        $NgoaiTe->bantienmat = 0;
                    } else {
                        $NgoaiTe->bantienmat = floatval(str_replace(',', '.', str_replace('.', '', $data["currency_sell_money"])));
                    }
                    if ($data["currency_buy_transfer"] == null || $data["currency_buy_transfer"] == "") {
                        $NgoaiTe->muachuyenkhoan = 0;
                    } else {
                        $NgoaiTe->muachuyenkhoan = floatval(str_replace(',', '.', str_replace('.', '', $data["currency_buy_transfer"])));
                    }
                    if ($data["currency_sell_money"] == null || $data["currency_sell_money"] == "") {
                        $NgoaiTe->banchuyenkhoan = 0;
                    } else {
                        $NgoaiTe->banchuyenkhoan = floatval(str_replace(',', '.', str_replace('.', '', $data["currency_sell_money"])));
                    }
                    $NgoaiTe->date = $data["date"];
                    $NgoaiTe->time = $data["time"];
                    //Save vào bảng Ngoại Tệ mới
                    $arr_diff = $this->tygiaNow($code1, 2, $NgoaiTe, $NgoaiTeCron);
                    if ($arr_diff) { // success
                        $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Cập nhật dữ liệu HSBC với đồng " . $data['currency_code'] . "  thành công \n";
                    } else { // failed
                        echo "Insert new money \n";
                        continue;
                    }
                }
                $Carbon = Carbon::now();
                $this->CreateLog($NgoaiTeCron, "Cập nhật thành công HSBC vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "hsbc");
                return response()->json(["message", "Get data success"]);
            } else {
                return response()->json(["message", "Get data faild, please check system again!"]);
            }
        }
    }
    /**
     * Custom function HSBC
     * */
    protected function getCurrencyCodeHSBC($CurrencyName)
    {
        $CodeHSBC = strstr($CurrencyName, "(");
        return $CodeHSBC;
    }
    protected function changeCharaterbsp($CurrencyName)
    {
        $Name = str_replace("&nbsp;", " ", $CurrencyName);
        return $Name;
    }
    protected function getDateHSBC($time)
    {
        $arrTime = explode(' ', $time);
        $Carbon = Carbon::now();
        return $Carbon;
    }
    protected function getTimeHSBC($time)
    {
        $arrTime = explode(' ', $time);
        $Carbon = new Carbon($arrTime[4]);
        return $Carbon;
    }
    /**
     * End more function HSBC
     * */
    /**
     * SHB function
     * */
    public function SHB($html, $NgoaiTeCron)
    {
        if ($html) {
            $time = null;
            $rows = $html->find("div.box_tigia div#show_exchange table.table-striped tr");
            $getTime = $html->find("div.search_tygia input[type=text]");
            foreach ($getTime as $value) {
                $time = Carbon::now();
            }
            $arrDataSHB = array();
            if (count($rows) > 0) {
                for ($i = 1; $i < count($rows); $i++) {
                    $currency_name = $rows[$i]->find("td", 1)->innertext;
                    $currency_code = $rows[$i]->find("td", 1)->innertext;
                    $currency_buy_money = $rows[$i]->find('td', 2)->innertext;
                    $currency_transfer = $rows[$i]->find('td', 3)->innertext;
                    $currency_sell_money = $rows[$i]->find('td', 4)->innertext;
                    $arrTemp = array(
                        "currency_code" => $currency_code,
                        "currency_name" => $currency_name,
                        "currency_buy_money" => $currency_buy_money,
                        "currency_buy_transfer" => $currency_transfer,
                        "currency_sell_money" => $currency_sell_money,
                        "date" => $time,
                        "time" => $time
                    );
                    array_push($arrDataSHB, $arrTemp);
                }
            } else {
                return null;
            }
            if (count($arrDataSHB) > 0) {
                foreach ($arrDataSHB as $data) {
                    $NgoaiTe = new NgoaiTe();
                    $NgoaiTe->code = $data["currency_code"];
                    $NgoaiTe->bank_id = 3;
                    $NgoaiTe->bank_code = "shb";
                    $NgoaiTe->bank_name = "SHB";
                    $NgoaiTe->bank_image = "/storage/userfiles/images/icons/SHB.png";
                    $NgoaiTe->symbol = $this->checkSymbol(str_replace(' ', '', str_replace('(', '', $data['currency_code'])));
                    $NgoaiTe->image = "/storage/currency/" . str_replace(' ', '', str_replace('(', '', $data['currency_code'])) . ".png";
                    $NgoaiTe->cron_id = $NgoaiTeCron;
                    $NgoaiTe->vname = "";
                    $NgoaiTe->ename = "";
                    if ($data["currency_buy_money"] == null || $data["currency_buy_money"] == "") {
                        $NgoaiTe->muatienmat = 0;
                    } else {
                        $NgoaiTe->muatienmat = floatval(str_replace(',', '', $data["currency_buy_money"]));
                    }
                    if ($data["currency_sell_money"] == null || $data["currency_sell_money"] == "") {
                        $NgoaiTe->bantienmat = 0;
                    } else {
                        $NgoaiTe->bantienmat = floatval(str_replace(',', '', $data["currency_sell_money"]));
                    }
                    if ($data["currency_buy_transfer"] == null || $data["currency_buy_transfer"] == "") {
                        $NgoaiTe->muachuyenkhoan = 0;
                    } else {
                        $NgoaiTe->muachuyenkhoan = floatval(str_replace(',', '', $data["currency_buy_transfer"]));
                    }
                    if ($data["currency_sell_money"] == null || $data["currency_sell_money"] == "") {
                        $NgoaiTe->banchuyenkhoan = 0;
                    } else {
                        $NgoaiTe->banchuyenkhoan = floatval(str_replace(',', '', $data["currency_sell_money"]));
                    }
                    $NgoaiTe->date = $data["date"];
                    $NgoaiTe->time = $data["time"];
                    //Save vào bảng Ngoại Tệ mới
                    $arr_diff = $this->tygiaNow($data["currency_code"], 3, $NgoaiTe, $NgoaiTeCron);
                    if ($arr_diff) { // success
                        $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Cập nhật dữ liệu SHB với đồng " . $data['currency_code'] . "  thành công \n";
                    } else { // failed
                        echo "Insert new money \n";
                        continue;
                    }
                }
                $Carbon = Carbon::now();
                $this->CreateLog($NgoaiTeCron, "Cập nhật thành công SHB vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "shb");
                return response()->json(["message", "Get dat success"]);
            } else {
                return null;
            }
        }
    }
    /**
     * Viettin bank
     * */
    public function vietinbank($html, $NgoaiTeCron)
    {
        if ($html) {
            $ex_odd = $html->find("tr .ex-odd td");
            $ex_odd_ar = array();
            foreach ($ex_odd as $key => $t1) {
                $ex_odd_ar[$key] = $t1->innertext;
            }
            $ex_even = $html->find("tr .ex-even td");
            $ex_even_ar = array();
            foreach ($ex_even as $key => $t2) {
                $ex_even_ar[$key] = $t2->innertext;
            }
            $result = array_merge($ex_odd_ar, $ex_even_ar);
            unset($result[1]);
            unset($result[6]);
            unset($result[11]);
            unset($result[16]);
            unset($result[21]);
            unset($result[26]);
            unset($result[31]);
            unset($result[36]);
            unset($result[41]);
            unset($result[45]);
            unset($result[46]);
            unset($result[47]);
            unset($result[48]);
            unset($result[49]);
            unset($result[51]);
            unset($result[56]);
            unset($result[61]);
            unset($result[65]);
            unset($result[66]);
            unset($result[67]);
            unset($result[68]);
            unset($result[69]);
            unset($result[71]);
            unset($result[76]);
            unset($result[81]);
            unset($result[86]);
            unset($result[91]);
            if (count($result) == 68) {
                $tygia_tho = array_chunk($result, ceil(count($result) / 17));
            } else {
                $tygia_tho = null;
            }
            $tygia = [];
            if ($tygia_tho) {
                foreach ($tygia_tho as $key => $tg) {
                    if (strpos($tg[1], '-')) {
                        $tg[1] = str_replace('-', '', $tg[1]);
                    }
                    if (strpos($tg[2], '-')) {
                        $tg[2] = str_replace('-', '', $tg[2]);
                    }
                    if (strpos($tg[3], '-')) {
                        $tg[3] = str_replace('-', '', $tg[3]);
                    }
                    $vl[0] = str_replace(',', '', str_replace('#', '', $tg[1]));
                    $vl[1] = str_replace(',', '', $tg[2]);
                    $vl[2] = str_replace(',', '', $tg[3]);
                    $vl[3] = null;
                    $tygia[$tg[0]] = $vl;
                }
                foreach ($tygia as $key => $value) {
                    $NgoaiTe = new NgoaiTe();
                    $NgoaiTe->code = strip_tags($key);
                    $NgoaiTe->bank_id = 6;
                    $NgoaiTe->bank_code = "vietin";
                    $NgoaiTe->bank_name = "VietinBank";
                    $NgoaiTe->bank_image = "/storage/userfiles/images/icons/VTB.png";
                    $NgoaiTe->symbol = $this->checkSymbol(strip_tags($key));
                    $NgoaiTe->image = "/storage/currency/" . strip_tags($key) . ".png";

                    $NgoaiTe->cron_id = $NgoaiTeCron;
                    $NgoaiTe->vname = "";
                    $NgoaiTe->ename = "";
                    if ($value[0] == "" || $value[0] == null) {
                        $NgoaiTe->muatienmat = 0;
                    } else {
                        $NgoaiTe->muatienmat = floatval($value[0]);
                    }
                    if ($value[3] == "" || $value[3] == null) {
                        $NgoaiTe->bantienmat = 0;
                    } else {
                        $NgoaiTe->bantienmat = floatval($value[3]);
                    }
                    if ($value[1] == "" || $value[1] == null) {
                        $NgoaiTe->muachuyenkhoan = 0;
                    } else {
                        $NgoaiTe->muachuyenkhoan = floatval($value[1]);
                    }
                    if ($value[2] == "" || $value[2] == null) {
                        $NgoaiTe->banchuyenkhoan = 0;
                    } else {
                        $NgoaiTe->banchuyenkhoan = floatval($value[2]);
                    }
                    $NgoaiTe->date = Carbon::now();
                    $NgoaiTe->time = Carbon::now();
                    //Save vào bảng Ngoại Tệ mới
                    $arr_diff = $this->tygiaNow(strip_tags($key), 6, $NgoaiTe, $NgoaiTeCron);
                    if ($arr_diff) { // success
                        $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Cập nhật dữ liệu ACB với đồng " . strip_tags($key) . "  thành công \n";
                    } else { // failed
                        echo "Insert new money \n";
                        continue;
                    }
                }
                $Carbon = Carbon::now();
                $this->CreateLog($NgoaiTeCron, "Cập nhật thành công Viettin bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "vietin");
            }
        }
    }
    /**
     * Sacombank bank
     * */
    public function sacombank($html, $NgoaiTeCron)
    {
        if ($html) {
            $sacom = $html->find('.tr-items td');
            foreach ($sacom as $key => $sc) {
                $gia[$key] = $sc->innertext;
            }
            if (count($gia) == 105) {
                $tygia_tho = array_chunk($gia, ceil(count($gia) / 21));
            } else {
                $tygia_tho = null;
            }
            if ($tygia_tho) {
                foreach ($tygia_tho as $key => $tg) {
                    if (strpos($tg[1], '.')) {
                        $tg[1] = str_replace('.', '', $tg[1]);
                    }
                    if (strpos($tg[2], '.')) {
                        $tg[2] = str_replace('.', '', $tg[2]);
                    }
                    if (strpos($tg[3], '.')) {
                        $tg[3] = str_replace('.', '', $tg[3]);
                    }
                    if (strpos($tg[4], '.')) {
                        $tg[4] = str_replace('.', '', $tg[4]);
                    }
                    $vl[0] = str_replace(',', '.', $tg[1]);
                    $vl[1] = str_replace(',', '.', $tg[2]);
                    $vl[2] = str_replace(',', '.', $tg[3]);
                    $vl[3] = str_replace(',', '.', $tg[4]);
                    $tygia[substr($tg[0], -3)] = $vl;
                }
            }
            foreach ($tygia as $key => $value) {
                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = strip_tags($key);
                $NgoaiTe->bank_id = 7;
                $NgoaiTe->bank_code = "sacombank";
                $NgoaiTe->bank_name = "SacomBank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/SAC.png";
                $NgoaiTe->symbol = $this->checkSymbol(strip_tags($key));
                $NgoaiTe->image = "/storage/currency/" . strip_tags($key) . ".png";

                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = "";
                $NgoaiTe->ename = "";
                if ($value[0] == "" || $value[0] == null) {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval($value[0]);
                }
                if ($value[3] == "" || $value[3] == null) {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval($value[3]);
                }
                if ($value[1] == "" || $value[1] == null) {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval($value[1]);
                }
                if ($value[2] == "" || $value[2] == null) {
                    $NgoaiTe->banchuyenkhoan = 0;
                } else {
                    $NgoaiTe->banchuyenkhoan = floatval($value[2]);
                }
                $NgoaiTe->date = Carbon::now();
                $NgoaiTe->time = Carbon::now();
                //Save vào bảng Ngoại Tệ mới
                $arr_diff = $this->tygiaNow(strip_tags($key), 7, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu Sacombank với đồng " . strip_tags($key) . " thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    continue;
                }
            }
            $Carbon = Carbon::now();
            $this->CreateLog($NgoaiTeCron, "Cập nhật thành công Sacom bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "sacombank");
        }
    }
    /**
     * Vietcom bank
     * */
    public function vietcombank($html, $NgoaiTeCron)
    {
        if ($html) {
            $vcb = simplexml_load_string($html);
            unset($vcb->DateTime);
            unset($vcb->Source);
            $tygia = [];
            foreach ($vcb as $tg) {
                $json = json_encode($tg);
                $array = json_decode($json, TRUE);
                foreach ($array as $key => $ar) {
                    $vl = [];
                    $vl[0] = $ar['Buy'];
                    $vl[1] = $ar['Transfer'];
                    $vl[2] = $ar['Sell'];
                    $vl[3] = null;
                    $tygia[$ar['CurrencyCode']] = $vl;
                }
            }
            foreach ($tygia as $key => $value) {
                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = strip_tags($key);
                $NgoaiTe->bank_id = 8;
                $NgoaiTe->bank_code = "vietcombank";
                $NgoaiTe->bank_name = "VietCombank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/VCB.png";
                $NgoaiTe->symbol = $this->checkSymbol(strip_tags($key));
                $NgoaiTe->image = "/storage/currency/" . strip_tags($key) . ".png";
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = "";
                $NgoaiTe->ename = "";
                if ($value[0] == "" || $value[0] == null) {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval($value[0]);
                }
                if ($value[3] == "" || $value[3] == null) {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval($value[3]);
                }
                if ($value[1] == "" || $value[1] == null) {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval($value[1]);
                }
                if ($value[2] == "" || $value[2] == null) {
                    $NgoaiTe->banchuyenkhoan = 0;
                } else {
                    $NgoaiTe->banchuyenkhoan = floatval($value[2]);
                }
                $NgoaiTe->date = Carbon::now();
                $NgoaiTe->time = Carbon::now();
                //Save vào bảng Ngoại Tệ mới
                $arr_diff = $this->tygiaNow(strip_tags($key), 8, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu Vietcombank với đồng " . strip_tags($key) . " thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    continue;
                }
            }
            $Carbon = Carbon::now();
            $this->CreateLog($NgoaiTeCron, "Cập nhật thành công Vietcombank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "vietcombank");
        }
    }
    /**
     * Dong a bank
     * */
    public function dongabank($html, $NgoaiTeCron)
    {
        if ($html) {
            $data = $html->find('table[class=table table-bordered table-striped table-hover]');
            $usd = $data[0]->find('td');
            $vang = $data[1]->find('td');
            $count = 0;
            $key = 0;
            foreach ($usd as $value) {
                $key++;
                if ($key == 8) {
                    $count = $count + 1;
                    $key = 1;
                }
                $v[$count][$key] = $value->innertext;
            }
            $t = array();
            foreach ($v as $val) {
                $t[$val[2]] = $val;
                unset($t[$val[2]][1]);
                unset($t[$val[2]][2]);
                unset($t[$val[2]][7]);
            }
            $ex = array();
            foreach ($t as $k => $q) {
                $a = 0;
                for ($i = 3; $i < 7; $i++) {
                    $ex[$k][$i] = str_replace('.', '', $q[$i]);
                    $ex[$k][$i] = str_replace(',', '.', $ex[$k][$i]);
                }
            }
            foreach ($ex as $key => $value) {
                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = strip_tags($key);
                $NgoaiTe->bank_id = 9;
                $NgoaiTe->bank_code = "dab";
                $NgoaiTe->bank_name = "DongABank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/DAB.png";
                $NgoaiTe->symbol = $this->checkSymbol(strip_tags($key));
                $NgoaiTe->image = "/storage/currency/" . strip_tags($key) . ".png";
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = "";
                $NgoaiTe->ename = "";
                if ($value[3] == "" || $value[3] == null) {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval($value[3]);
                }
                if ($value[5] == "" || $value[5] == null) {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval($value[5]);
                }
                if ($value[4] == "" || $value[4] == null) {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval($value[4]);
                }
                if ($value[6] == "" || $value[6] == null) {
                    $NgoaiTe->banchuyenkhoan = 0;
                } else {
                    $NgoaiTe->banchuyenkhoan = floatval($value[6]);
                }
                $NgoaiTe->date = Carbon::now();
                $NgoaiTe->time = Carbon::now();
                //Save vào bảng Ngoại Tệ mới
                $arr_diff = $this->tygiaNow(strip_tags($key), 9, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu ACB với đồng " . strip_tags($key) . "  thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    continue;
                }
            }
            $Carbon = Carbon::now();
            $this->CreateLog($NgoaiTeCron, "Cập nhật thành công Đông Á Bank vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "dab")  . "\n";
        }
    }
    protected function checkIssetMoney($money)
    {
        if ($money == "") {
            return null;
        } else {
            return $money;
        }
    }
    /**
     * insert vname and ename to currency
     * param: code
     * @return more
     * */
    protected function insertNameCurrency()
    {
        $NgoaiTe = NgoaiTe::select("code", "vname", "ename")->get();
        $CurrencyCode = CurrencyCode::select("code", "name", "vname")->get();
        foreach ($NgoaiTe as $ngoaite) {
            if ($ngoaite->vname == null || $ngoaite->ename == null) {
                foreach ($CurrencyCode as $currencycode) {
                    if ($ngoaite->code == $currencycode->code || "(" . $currencycode->code . ")" ==  $ngoaite->code) {
                        $ngoaite->ename = $currencycode->name;
                        $ngoaite->vname = $currencycode->vname;
                        $ngoaite->save();
                    } else { }
                }
            } else { }
        }
    }


    public function agribank($html, $NgoaiTeCron)
    {

        if ($html) {
            $table = $html->find("#tblTG table");
            if (count($table) > 0) {
                $tr = $html->find("#tblTG table tr");
                for ($i = 1; $i < count($tr); $i++) {
                    $mangoaite = str_replace(',', '', $tr[$i]->find("td", 0)->innertext);
                    $muatienmat = str_replace(',', '', $tr[$i]->find("td", 1)->innertext);
                    $muachuyenkhoan = str_replace(',', '', $tr[$i]->find("td", 2)->innertext);
                    $bantienmat = str_replace(',', '', $tr[$i]->find("td", 3)->innertext);

                    $NgoaiTe = new NgoaiTe();
                    $NgoaiTe->code = strip_tags($mangoaite);
                    $NgoaiTe->bank_id = 13;
                    $NgoaiTe->bank_code = "agribank";
                    $NgoaiTe->bank_name = "Argibank";
                    $NgoaiTe->bank_image = "/storage/userfiles/images/icons/argibank.png";
                    $NgoaiTe->symbol = $this->checkSymbol(strip_tags($mangoaite));
                    $NgoaiTe->image = "/storage/currency/" . strip_tags($mangoaite) . ".png";
                    $NgoaiTe->cron_id = $NgoaiTeCron;
                    $NgoaiTe->vname = "";
                    $NgoaiTe->ename = "";
                    if ($muatienmat == "" || $muatienmat == null) {
                        $NgoaiTe->muatienmat = 0;
                    } else {
                        $NgoaiTe->muatienmat = floatval($muatienmat);
                    }
                    if ($muachuyenkhoan == "" || $muachuyenkhoan == null) {
                        $NgoaiTe->bantienmat = 0;
                    } else {
                        $NgoaiTe->bantienmat = floatval($muachuyenkhoan);
                    }
                    if ($bantienmat == "" || $bantienmat == null) {
                        $NgoaiTe->muachuyenkhoan = 0;
                    } else {
                        $NgoaiTe->muachuyenkhoan = floatval($bantienmat);
                    }
                    if ($bantienmat == "" || $bantienmat == null) {
                        $NgoaiTe->banchuyenkhoan = 0;
                    } else {
                        $NgoaiTe->banchuyenkhoan = floatval($bantienmat);
                    }
                    $Carbon = Carbon::now();
                    $NgoaiTe->date = $Carbon;
                    $NgoaiTe->time = $Carbon;
                    //Save vào bảng Ngoại Tệ mới
                    $arr_diff = $this->tygiaNow(strip_tags($mangoaite), 13, $NgoaiTe, $NgoaiTeCron);
                    if ($arr_diff) { // success
                        $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Cập nhật dữ liệu Argibank với đồng " . strip_tags($mangoaite) . " thành công \n";
                    } else { // failed
                        echo "Insert new money \n";
                        continue;
                    }
                }
            } else {
                return "datanone";
            }
        } else { }
    }

    public function eximbank($html, $NgoaiTeCron)
    {

        if ($html) {

            $table = $html->find("table");
            $data = $table[9];
            $rows = $data->find("tr");

            for ($i = 0; $i < count($rows); $i += 2) {

                $vname = strip_tags($rows[$i]->find("span", 0));

                if ($vname === "Đô-la Mỹ (USD 5-20)" || $vname === "Đô-la Mỹ (Dưới 5 USD)") {
                    continue;
                }
                $mangoaite = "";
                if ($vname === "Đô-la Mỹ (USD 50-100)") {
                    $mangoaite = "USD";
                }
                if ($vname === "Bảng Anh") {
                    $mangoaite = "GDP";
                }
                if ($vname === "Đô-la Hồng Kông") {
                    $mangoaite = "HKD";
                }
                if ($vname === "Franc Thụy Sĩ") {
                    $mangoaite = "CHF";
                }
                if ($vname === "Yên Nhật") {
                    $mangoaite = "JPY";
                }
                if ($vname === "Ðô-la Úc") {
                    $mangoaite = "AUD";
                }
                if ($vname === "Ðô-la Canada") {
                    $mangoaite = "CAD";
                }
                if ($vname === "Ðô-la Singapore") {
                    $mangoaite = "SGD";
                }
                if ($vname === "Đồng Euro") {
                    $mangoaite = "EUR";
                }
                if ($vname === "Ðô-la New Zealand") {
                    $mangoaite = "NZD";
                }
                if ($vname === "Bat Thái Lan") {
                    $mangoaite = "THB";
                }
                if ($vname === "Nhân Dân Tệ Trung Quốc") {
                    $mangoaite = "CNY";
                }
                $muatienmat = str_replace(',', '', strip_tags($rows[$i]->find("span", 1)));
                $muachuyenkhoan = str_replace(',', '', strip_tags($rows[$i]->find("span", 2)));
                $bantienmat = str_replace(',', '', strip_tags($rows[$i]->find("span", 3)));

                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = $mangoaite;
                $NgoaiTe->bank_id = 14;
                $NgoaiTe->bank_code = "eximbank";
                $NgoaiTe->bank_name = "Eximbank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/exim.png";
                $NgoaiTe->symbol = $this->checkSymbol($mangoaite);
                $NgoaiTe->image = "/storage/currency/" . $mangoaite . ".png";
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = "";
                $NgoaiTe->ename = "";
                if ($muatienmat == "" || $muatienmat == null) {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval($muatienmat);
                }
                if ($muachuyenkhoan == "" || $muachuyenkhoan == null) {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval($muachuyenkhoan);
                }
                if ($bantienmat == "" || $bantienmat == null) {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval($bantienmat);
                }
                if ($bantienmat == "" || $bantienmat == null) {
                    $NgoaiTe->banchuyenkhoan = 0;
                } else {
                    $NgoaiTe->banchuyenkhoan = floatval($bantienmat);
                }
                $Carbon = Carbon::now();
                $NgoaiTe->date = $Carbon;
                $NgoaiTe->time = $Carbon;
                $arr_diff = $this->tygiaNow(strip_tags($mangoaite), 14, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu Eximbank với đồng " . $mangoaite . "  thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    continue;
                }
            }
        }
    }
    public function mbbank($htmlMBBank, $NgoaiTeCron)
    {

        if ($htmlMBBank) {
            $table = $htmlMBBank->find("div.table-responsive table", 0);
            $tr = $table->find("tr");

            for ($i = 2; $i < count($tr); $i++) {
                $Code = $tr[$i]->find("th", 0)->innertext;

                if ($Code === "USD (USD 5 - 20)" || $Code === "USD (Dưới 5 USD)") {
                    $Code = "USDDelete";
                }
                if ($Code == "USD (USD 50-100)") {
                    $Code = "USD";
                } else { }
                //                    $Code = $tr[$i]->find("th",0)->innertext;
                $muatienmat = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 0)->innertext));
                $muachuyenkhoan = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 1)->innertext));
                $bantienmat = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 2)->innertext));

                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = $Code;
                $NgoaiTe->bank_id = 15;
                $NgoaiTe->bank_code = "mbbank";
                $NgoaiTe->bank_name = "MBank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/mbank.png";
                $NgoaiTe->symbol = $this->checkSymbol(strip_tags($Code));
                $NgoaiTe->image = "/storage/currency/" . $Code . ".png";
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = $Code;
                if ($muatienmat == 0 || $muatienmat == null || $muatienmat == "-") {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = $muatienmat;
                }
                if ($muachuyenkhoan == 0 || $muachuyenkhoan == null || $muachuyenkhoan == "-") {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = $muachuyenkhoan;
                }
                if ($bantienmat == 0 || $bantienmat == null || $bantienmat == "-") {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = $bantienmat;
                    $NgoaiTe->banchuyenkhoan = $bantienmat;
                }
                $Carbon = new Carbon();
                $NgoaiTe->date = $Carbon;
                $NgoaiTe->time = $Carbon;
                $arr_diff = $this->tygiaNow($Code, 15, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu MBBank với đồng " . $Code . " thành công \n";
                    if ($NgoaiTe->save()) {
                        DB::table('ngoaite_today')->where('bank_id', 15)->where('code', 'USDDelete')->delete();
                        DB::table('ngoaite_today')->where('bank_id', 15)->where("code", "USD (USD 5 - 20)")->delete();
                        DB::table('ngoaite_today')->where('cron_id', '!=', $NgoaiTeCron)->delete();
                        $this->CreateLog($NgoaiTeCron, "Cập nhật MBBank thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "Mbbank")  . "\n";
                    } else {
                        $this->CreateLog($NgoaiTeCron, "Cập nhật MBBank không thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "Mbbank") . "\n";
                    }
                } else { // failed
                    echo "Insert new money \n";
                    $this->CreateLog($NgoaiTeCron, "Cập nhật MBBank không thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "Mbbank") . "\n";
                    continue;
                }
            }
        } else {
            echo "K kết nối đc đến máy chủ dữ liệu \n";
        }
    }

    protected function tpBank($html, $NgoaiTeCron)
    {

        if ($html) {
            $table = $html->find("div.table-responsive", 0);
            $tr = $table->find("tr");

            for ($i = 2; $i < count($tr); $i++) {
                $Code = $tr[$i]->find("th", 0)->innertext;
                $Ename = $tr[$i]->find("th", 1)->innertext;
                $muatienmat = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 0)->innertext));
                $muachuyenkhoan = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 1)->innertext));
                $bantienmat = str_replace(',', '.', str_replace('.', '', $tr[$i]->find("td", 2)->innertext));

                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->code = $Code;
                $NgoaiTe->bank_id = 4;
                $NgoaiTe->bank_code = "tpb";
                $NgoaiTe->bank_name = "TBP";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/TPB.png";
                $NgoaiTe->symbol = $Code;
                $NgoaiTe->image = "/storage/currency/" . $Code . ".png";
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->vname = $Ename;
                $NgoaiTe->ename = $Ename;
                if ($muatienmat == 0 || $muatienmat == null || $muatienmat == "-") {
                    $NgoaiTe->muatienmat = 0;
                } else {
                    $NgoaiTe->muatienmat = floatval($muatienmat);
                }
                if ($muachuyenkhoan == 0 || $muachuyenkhoan == null || $muachuyenkhoan == "-") {
                    $NgoaiTe->muachuyenkhoan = 0;
                } else {
                    $NgoaiTe->muachuyenkhoan = floatval($muachuyenkhoan);
                }
                if ($bantienmat == 0 || $bantienmat == null || $bantienmat == "-") {
                    $NgoaiTe->bantienmat = 0;
                } else {
                    $NgoaiTe->bantienmat = floatval($bantienmat);
                    $NgoaiTe->banchuyenkhoan = floatval($bantienmat);
                }
                $Carbon = new Carbon();
                $NgoaiTe->date = $Carbon;
                $NgoaiTe->time = $Carbon;
                $arr_diff = $this->tygiaNow($Code, 4, $NgoaiTe, $NgoaiTeCron);
                if ($arr_diff) { // success
                    $NgoaiTe->muatienmat_diff = $arr_diff["tyle_muatienmat"];
                    $NgoaiTe->bantienmat_diff = $arr_diff["tyle_bantienmat"];
                    $NgoaiTe->muachuyenkhoan_diff = $arr_diff["tyle_muachuyenkhoan"];
                    $NgoaiTe->banchuyenkhoan_diff = $arr_diff["tyle_banchuyenkhoan"];
                    $NgoaiTe->save();
                    echo "Cập nhật dữ liệu TPBank với đồng " . $Code . " thành công \n";
                } else { // failed
                    echo "Insert new money \n";
                    $this->CreateLog($NgoaiTeCron, "Cập nhật TPBank không thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "TPB") . "\n";
                    continue;
                }
            }
        }
        $this->CreateLog($NgoaiTeCron, "Cập nhật TPbank thành công vào lúc: " . $Carbon->format('h:i:s d/m/Y'), "TPB") . "\n";
    }

    public function scb($SimpleHTMLDOM, $NgoaiTeCron)
    {
        $post_url = 'https://www.scb.com.vn/Handlers/GetForeignExchangeCount.aspx?';
        $dataPost = array();
        $today = date('d/m/Y');
        $dataPost['date'] = $today;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $post_url, [
            'headers' => ['content-type' => 'application/json', 'cache-control' => 'no-cache'],
            'body' => \GuzzleHttp\json_encode($dataPost)
        ]);
        $statusCode = $response->getStatusCode();
        $result_ncc = $response->getBody()->getContents();
        if ($statusCode == 200) {
            $result = json_decode($result_ncc, true);
            try {
                $url = 'https://www.scb.com.vn/Handlers/GetForeignExchange.aspx?';
                $post = array();
                $post['tableno'] = $result["tableno"][0]['id'];
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.scb.com.vn/Handlers/GetForeignExchange.aspx?");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "tableno=" . $post['tableno']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $server_output = curl_exec($ch);
                    curl_close($ch);
                } catch (\Exception $e) {
                    $server_output = null;
                    echo $e;
                }

                if ($server_output) {
                    $html = $SimpleHTMLDOM->str_get_html($server_output);
                    if ($html) {
                        $table = $html->find('table', 0);
                        $tr = $table->find('tr');
                        $CarbonNow = Carbon::now();
                        for ($i = 1; $i < count($tr); $i++) {
                            $mangoaite = strip_tags(str_replace(']', '', str_replace('[', '', str_replace('-', '', $tr[$i]->find('td', 0)))));
                            $muatienmat = strip_tags(str_replace(',', '', $tr[$i]->find('td', 1)));
                            $bantienmat = strip_tags(str_replace(',', '', $tr[$i]->find('td', 2)));
                            $muachuyenkhoan = strip_tags(str_replace(',', '', $tr[$i]->find('td', 3)));
                            $banchuyenkhoan = strip_tags(str_replace(',', '', $tr[$i]->find('td', 4)));
                            if ($mangoaite === "USD520" || $mangoaite === "USD50100") {
                                continue;
                            }
                            if ($muatienmat == "") {
                                $muatienmat = 0;
                            }
                            if ($bantienmat == "") {
                                $bantienmat = 0;
                            }
                            if ($muachuyenkhoan == "") {
                                $muachuyenkhoan = 0;
                            }
                            if ($banchuyenkhoan == "") {
                                $banchuyenkhoan = 0;
                            }
                            $cronJobNow = DB::table('ngoaite_cron')->orderBy('id', 'DESC')->first();

                            $insertSCB = new NgoaiTe();
                            $insertSCB->cron_id = $NgoaiTeCron;
                            $insertSCB->code = $mangoaite;
                            $insertSCB->bank_id = 16;
                            $insertSCB->bank_code = "scb";
                            $insertSCB->bank_name = "SCB";
                            $insertSCB->bank_image = "/storage/userfiles/images/icons/scb.png";
                            $insertSCB->vname = $mangoaite;
                            $insertSCB->ename = $mangoaite;
                            $insertSCB->symbol = $this->checkSymbol($mangoaite);
                            $insertSCB->image = "/storage/currency/" . $mangoaite . ".png";
                            $insertSCB->muatienmat = $muatienmat;
                            $insertSCB->bantienmat = $bantienmat;
                            $insertSCB->muachuyenkhoan = $muachuyenkhoan;
                            $insertSCB->banchuyenkhoan = $banchuyenkhoan;
                            $insertSCB->default = 1;
                            $insertSCB->date = $CarbonNow;
                            $insertSCB->time = $CarbonNow;
                            try {
                                $updateDiff = $this->tygiaNow($mangoaite, 16, $insertSCB, $NgoaiTeCron);
                                if ($updateDiff) {
                                    $insertSCB->muatienmat_diff = $updateDiff["tyle_muatienmat"];
                                    $insertSCB->bantienmat_diff = $updateDiff["tyle_bantienmat"];
                                    $insertSCB->muachuyenkhoan_diff = $updateDiff["tyle_muachuyenkhoan"];
                                    $insertSCB->banchuyenkhoan_diff = $updateDiff["tyle_banchuyenkhoan"];
                                    $insertSCB->save();
                                    echo "Thêm thành công ngoại tệ: " . $mangoaite . " của ngân hàng SCB \n";
                                } else {
                                    echo "Insert new money \n";
                                }
                            } catch (\Exception $e) {
                                return $e;
                            }
                        }
                    } else {
                        echo "null";
                    }
                } else {
                    echo "Cannot find data the links";
                }
            } catch (\Exception $ex) { }
        }
    }

    public function msb($html, $NgoaiTeCron)
    {

        if ($html) {
            $table = $html->find(".exchange-rate .content", 0);

            for ($i = 1; $i <= 12; $i++) {
                $tr = $table->find("tr", $i);
                $mangoaite = html_entity_decode(strip_tags($tr->find("td", 0)->innertext));
                $muatienmat = floatval(str_replace(',', '', strip_tags($tr->find("td", 1))));
                $bantienmat = floatval(str_replace(',', '', strip_tags($tr->find("td", 2))));

                if ($muatienmat === "-" || $muatienmat == 0 || $muatienmat === "0") {
                    $muatienmat == 0;
                }
                if ($bantienmat === "-" || $bantienmat == 0 || $bantienmat === "0") {
                    $bantienmat == 0;
                }

                $Carbon = Carbon::now();

                $NgoaiTe = new NgoaiTe();
                $NgoaiTe->cron_id = $NgoaiTeCron;
                $NgoaiTe->code = $mangoaite;
                $NgoaiTe->bank_id = 17;
                $NgoaiTe->bank_code = "msb";
                $NgoaiTe->bank_name = "MaritimeBank";
                $NgoaiTe->bank_image = "/storage/userfiles/images/icons/MSB.jpg";
                $NgoaiTe->vname = $mangoaite;
                $NgoaiTe->ename = $mangoaite;
                $NgoaiTe->symbol = $this->checkSymbol($mangoaite);
                $NgoaiTe->image = "/storage/currency/" . $mangoaite . ".png";
                $NgoaiTe->muatienmat = $muatienmat;
                $NgoaiTe->bantienmat = $bantienmat;
                $NgoaiTe->muachuyenkhoan = $muatienmat;
                $NgoaiTe->banchuyenkhoan = $bantienmat;
                $NgoaiTe->default = 1;
                $NgoaiTe->date = $Carbon;
                $NgoaiTe->time = $Carbon;
                try {
                    $updateDiff = $this->tygiaNow($mangoaite, 17, $NgoaiTe, $NgoaiTeCron);
                    if ($updateDiff) {
                        $NgoaiTe->muatienmat_diff = $updateDiff["tyle_muatienmat"];
                        $NgoaiTe->bantienmat_diff = $updateDiff["tyle_bantienmat"];
                        $NgoaiTe->muachuyenkhoan_diff = $updateDiff["tyle_muachuyenkhoan"];
                        $NgoaiTe->banchuyenkhoan_diff = $updateDiff["tyle_banchuyenkhoan"];
                        $NgoaiTe->save();
                        echo "Thêm thành công ngoại tệ: " . $mangoaite . " của ngân hàng Maritime Bank \n";
                    } else {
                        echo "Insert new money \n";
                    }
                } catch (\Exception $e) {
                    return $e;
                }
            }
        } else {
            echo "Không tìm được dữ liệu ở đường dẫn trên.";
        }
    }


    /**
     * Thống kê tăng giảm bao nhiêu % đối với các tỷ giá
     * */
    protected function tygiaNow($code, $bank_id, $NgoaiTe, $NgoaiTeCron)
    {
        if ($code != null && $bank_id != null) {
            $tygiacu = NgoaiTe::where("code", "=", $code)
                ->where("bank_id", "=", $bank_id)
                ->orderBy('id', 'DESC')
                ->first();

            if ($tygiacu) {
                if ($tygiacu->muatienmat != null) {
                    if ($NgoaiTe->muatienmat != 0 || $NgoaiTe->muatienmat != null) {
                        $tyle_muatienmat = (float) $NgoaiTe->muatienmat - $tygiacu->muatienmat;
                    } else {
                        $tyle_muatienmat = null;
                    }
                } else {
                    $tyle_muatienmat = null;
                }
                if ($tygiacu->muachuyenkhoan != null) {
                    if ($NgoaiTe->muachuyenkhoan != 0 || $NgoaiTe->muachuyenkhoan != null) {
                        $tyle_muachuyenkhoan = (float) $NgoaiTe->muachuyenkhoan - $tygiacu->muachuyenkhoan;
                    } else {
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_muachuyenkhoan = null;
                }
                if ($tygiacu->bantienmat != null) {
                    if ($NgoaiTe->bantienmat != 0 || $NgoaiTe->bantienmat != null) {
                        $tyle_bantienmat = (float) $NgoaiTe->bantienmat - $tygiacu->bantienmat;
                    } else {
                        $tyle_bantienmat = null;
                    }
                } else {
                    $tyle_bantienmat = null;
                }
                if ($tygiacu->banchuyenkhoan != null) {
                    if ($NgoaiTe->banchuyenkhoan != 0 || $NgoaiTe->banchuyenkhoan != null) {
                        $tyle_banchuyenkhoan = (float) $NgoaiTe->banchuyenkhoan - $tygiacu->banchuyenkhoan;
                    } else {
                        $tyle_muachuyenkhoan = null;
                    }
                } else {
                    $tyle_banchuyenkhoan = null;
                }
                $check = DB::table('ngoaite_today')
                    ->where('code', '=', $code)
                    ->where('bank_id', '=', $bank_id)
                    ->orderBy('id', 'DESC')
                    ->delete();

                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id" => $NgoaiTeCron,
                    "code" => $code,
                    'bank_id' => $bank_id,
                    'bank_code' => $NgoaiTe->bank_code,
                    'bank_name' => $NgoaiTe->bank_name,
                    'bank_image' => $NgoaiTe->bank_image,
                    'symbol' => $NgoaiTe->symbol,
                    'image' => $NgoaiTe->image,
                    'vname' => strip_tags($NgoaiTe->vname),
                    'ename' => strip_tags($NgoaiTe->ename),
                    'muatienmat' => $NgoaiTe->muatienmat,
                    'tyle_muatienmat' => $tyle_muatienmat,
                    'muachuyenkhoan' => $NgoaiTe->muachuyenkhoan,
                    'tyle_muachuyenkhoan' => $tyle_muachuyenkhoan,
                    'bantienmat' => $NgoaiTe->bantienmat,
                    'tyle_bantienmat' => $tyle_bantienmat,
                    'banchuyenkhoan' => $NgoaiTe->banchuyenkhoan,
                    'tyle_banchuyenkhoan' => $tyle_banchuyenkhoan,
                    'date' => $NgoaiTe->date,
                    'time' => $NgoaiTe->time
                ]);
                if ($NgoaiTeToDay) {
                    $arr_diff = array(
                        "tyle_muatienmat" => $tyle_muatienmat,
                        "tyle_muachuyenkhoan" => $tyle_muachuyenkhoan,
                        "tyle_bantienmat" => $tyle_bantienmat,
                        "tyle_banchuyenkhoan" => $tyle_banchuyenkhoan
                    );
                    return $arr_diff;
                }
            } else {
                $NgoaiTeToDay = DB::table("ngoaite_today")->insert([
                    "cron_id" => $NgoaiTeCron,
                    "code" => $code,
                    'bank_id' => $bank_id,
                    'bank_code' => $NgoaiTe->bank_code,
                    'bank_name' => $NgoaiTe->bank_name,
                    'bank_image' => $NgoaiTe->bank_image,
                    'symbol' => $NgoaiTe->symbol,
                    'image' => $NgoaiTe->image,
                    'vname' => $NgoaiTe->vname,
                    'ename' => $NgoaiTe->ename,
                    'muatienmat' => $NgoaiTe->muatienmat,
                    'tyle_muatienmat' => null,
                    'muachuyenkhoan' => $NgoaiTe->muachuyenkhoan,
                    'tyle_muachuyenkhoan' => null,
                    'bantienmat' => $NgoaiTe->bantienmat,
                    'tyle_bantienmat' => null,
                    'banchuyenkhoan' => $NgoaiTe->banchuyenkhoan,
                    'tyle_banchuyenkhoan' => null,
                    'date' => $NgoaiTe->date,
                    'time' => $NgoaiTe->time
                ]);
                if ($NgoaiTeToDay) {
                    return null;
                }
            }
        }
    }
    protected function getCurrencyCode()
    {
        $CurrencyCode = CurrencyCode::select("code", "vname")->get();
        $arrayCurrencyCode = array();
        foreach ($CurrencyCode as $key => $value) {
            if ($value->code == "EUR") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "GBP") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "JPY") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "KRW") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "HKD") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "CHF") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "THB") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "AUD") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "CAD") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "SGD") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "SEK") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "LAK") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "DKK") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "NOK") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "CNY") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "RUB") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "NZD") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "MYR") {
                array_push($arrayCurrencyCode, $value->code);
            } else if ($value->code == "TWD") {
                array_push($arrayCurrencyCode, $value->code);
            }
        }
        return $arrayCurrencyCode;
    }

    protected function checkSymbol($code)
    {
        if ($code == "EUR") {
            return "&#8364;";
        } else if ($code == "GBP") {
            return "&#163;";
        } else if ($code == "JPY") {
            return "&#165;";
        } else if ($code == "KRW") {
            return "&#8361;";
        } else if ($code == "HKD") {
            return "&#65504;";
        } else if ($code == "CHF") {
            return "&#65504;";
        } else if ($code == "THB") {
            return "&#3647;";
        } else if ($code == "AUD") {
            return "&#8371;";
        } else if ($code == "CAD") {
            return "&#36;";
        } else if ($code == "SGD") {
            return "&#36;";
        } else if ($code == "SEK") {
            return "&#8364;";
        } else if ($code == "LAK") {
            return "&#8365;";
        } else if ($code == "DKK") {
            return "&#36;";
        } else if ($code == "NOK") {
            return "&#36;";
        } else if ($code == "CNY") {
            return "&#165;";
        } else if ($code == "RUB") {
            return "&#8381;";
        } else if ($code == "NZD") {
            return "&#36;";
        } else if ($code == "MYR") {
            return "&#8357;";
        } else if ($code == "TWD") {
            return "&#36;";
        }
    }
}
