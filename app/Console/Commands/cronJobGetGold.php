<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Helpers\SimpleHtmlDom;
use DB;

class cronJobGetGold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronJob:getGold';

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
        session_start();
        $Carbon = Carbon::now();

        $dom = new SimpleHtmlDom();

        $this->sjc();

        try {
            $html = $dom->file_get_html('http://btmc.vn/api-get-list.htm');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->btmc($html, $dom);

        try {
            $html = $dom->file_get_html('http://vangmieng.pnj.com.vn/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->pnj($html, $dom);

        try {
            $html = $dom->file_get_html('http://gold.phuquy.com.vn/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->phuquy($html, $dom);

        try {
            $html = $dom->file_get_html('https://webgia.com/gia-vang/pnj/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->pnj_webgia($html, $dom);

        try {
            $html = $dom->file_get_html('https://webgia.com/gia-vang/phu-quy/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->phuquy_webgia($html, $dom);

        try {
            $html = $dom->file_get_html('https://webgia.com/gia-vang/bao-tin-minh-chau/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->btmc_webgia($html, $dom);

        try {
            $html = $dom->file_get_html('http://giavang.doji.vn/');
        } catch (\Exception $e) {
            $html = null;
        }
        $this->doji($html, $dom);

        /**
         * Tỷ giá vàng của các ngân hàng
         * */
        $urlEximbank = "https://eximbank.com.vn/WebsiteExrate/Gold_vn_2012.aspx";

        try {
            $htmlEximbank = $dom->file_get_html($urlEximbank);
        } catch (\Exception $exception) {
            $htmlEximbank = null;
        }
        $this->sjcEximbank($htmlEximbank, $dom);

        $urlSacombank = "https://sacombank.com.vn/company/Pages/ty-gia.aspx";
        try {
            $htmlSacombank = $dom->file_get_html($urlSacombank);
        } catch (\Exception $exception) {
            $htmlSacombank = null;
        }
        $this->sjcSacombank($htmlSacombank, $dom);

        $urltg = "https://www.kitco.com/";
        try {
            $htmltg = $dom->file_get_html($urltg);
        } catch (\Exception $exception) {
            $htmltg = null;
        }
        $this->giavangtg($htmltg, $dom);

        $urlVietinbank = "http://vietinbankgold.vn/gia-vang.html";
        try {
            $htmlVietinbank = $dom->file_get_html($urlVietinbank);
        } catch (\Exception $exception) {
            $htmlVietinbank = null;
        }
        $this->sjcVietinbank($htmlVietinbank, $dom);

        $urlTechcombank = "https://techcombank.com.vn/cong-cu-tien-ich/ti-gia/ti-gia-vang";
        try {
            $htmlTechcombank = $dom->file_get_html($urlTechcombank);
        } catch (\Exception $exception) {
            $htmlTechcombank = null;
        }
        $this->sjcTechcombank($htmlTechcombank, $dom);
    }

    public function checkCron($key = '', $nguon = '')
    {
        $check = DB::table('tygiavang_cron')
            ->where('cron_key', $key)
            ->where('nguon', $nguon)
            ->first();
        if (!$check) {
            return false;
        }
        return true;
    }

    public function insertCron($key = '', $nguon = '', $slug = '')
    {
        $now = Carbon::now();
        $insert_id = DB::table('tygiavang_cron')->insertGetId([
            'cron_key' => $key,
            'nguon'    => $nguon,
            'slug'     => $slug
        ]);
        return $insert_id;
    }

    public function insertVang($data, $OilCron)
    {
        $oidData = DB::table('tygiavang_data')
            ->where('slug', '=', $data['slug'])
            ->where('cron_id', '=', $OilCron->id)
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($oidData as $value) {
            $tyle_mua = 0;
            $tyle_ban = 0;
            if ($value->loai === $data['loai'] && $value->tinhthanh === $data['tinhthanh']) {
                if ($data['mua'] > 0) {
                    $tyle_mua = 100 - (($value->mua / (float) $data['mua']) * 100);
                } else {
                    $tyle_mua = 0;
                }
                if ($data['ban'] > 0) {
                    $tyle_ban = 100 - (($value->ban / (float) $data['ban']) * 100);
                } else {
                    $tyle_ban = 0;
                }
            } else {
                continue;
            }
        }

        $insertDB = DB::table('tygiavang_data')->insert([
            'cron_id' => $data['cron_id'],
            'tieude'    => $data['tieude'],
            'slug' => $data['slug'],
            'loai'    => $data['loai'],
            'tinhthanh' => $data['tinhthanh'],
            'mua'  => (float) $data['mua'],
            'tyle_mua' => (float) $tyle_mua,
            'ban'  => (float) $data['ban'],
            'tyle_ban' => (float) $tyle_ban,
            'data' => $data['data'],
            'donvi' => $data['donvi'],
        ]);
        if ($insertDB) {
            echo "Insert" . $data['slug'] . "success;  ";
        } else {
            echo "Insert" . $data['slug'] . "failed";
        }
    }

    public function btmc($html, $dom)
    {

        if ($html) {
            $updated = $html->find('.leftPriceGV', 0);
            $updated = str_replace('Cập nhật ', '', $updated->plaintext);
            $updated = str_replace(' ngày ', '', $updated);
            $updated = str_replace(' ', '', $updated);

            $OilCron = DB::table('tygiavang_cron')->where('slug', '=', 'bao-tin-minh-chau')->orderBy('id', 'DESC')->first();

            $insert_id = $this->insertCron($updated, 'btmc.vn/api-get-list.htm', 'bao-tin-minh-chau');

            $donvi = 'Nghìn đồng / lượng';
            $_SESSION['tinhthanh'] = '';
            $trs = $html->find('table tbody tr');
            foreach ($trs as $tr) {
                $tds = $dom->str_get_html($tr->innertext);
                $td = $tds->find('td');

                if (count($td) == 6) {
                    $img = $dom->str_get_html($td[0]->outertext);
                    $filename =  str_replace('/Data/upload/files/AnhThuongPham/', '', $img->find('img', 0)->src);
                    switch ($filename) {
                        case 'vangrongthanglong.png':
                            $name =  'Vàng rồng Thăng Long';
                            break;
                        case 'vangBTMC.png':
                            $name =  'Vàng BTMC';
                            break;
                        case 'vangHTBT.png':
                            $name =  'Vàng HTMC';
                            break;
                        case 'vangSJC.png':
                            $name = 'Vàng SJC';
                            break;
                        case 'VangTT.png';
                            $name = 'Vàng thị trường';
                            break;
                        case 'vangBIMC.png';
                            $name = 'Vàng nguyên liệu BTMC';
                            break;
                        case 'VangNLTT.png':
                            $name = 'Vàng nguyên liệu thị trường';
                            break;
                        default:
                            $name = 'Chưa phân loại';
                    }
                    $_SESSION['tinhthanh'] = $name;

                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $td[1]->plaintext;
                    $data['slug'] = "bao-tin-minh-chau";
                    $data['loai'] = $td[1]->plaintext;
                    $data['tinhthanh'] = $_SESSION['tinhthanh'];
                    $data['mua']  = str_replace('.', '', $td[3]->plaintext);
                    $data['ban'] = str_replace('.', '', $td[4]->plaintext);
                    $data['data'] = '';
                    $data['donvi'] = $donvi;
                    $this->insertVang($data, $OilCron);
                }
                if (count($td) == 5) {

                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $td[0]->plaintext;
                    $data['slug'] = "bao-tin-minh-chau";
                    $data['loai'] = $td[0]->plaintext;
                    $data['tinhthanh'] = $_SESSION['tinhthanh'];
                    $data['mua']  = str_replace('.', '', $td[2]->plaintext);
                    $data['ban'] = str_replace('.', '', $td[3]->plaintext);
                    $data['data'] = '';
                    $data['donvi'] = $donvi;
                    $this->insertVang($data, $OilCron);
                }
            }
            return 'Btmc Updated';
        }
        return 'html null';
    }

    //Phu Nhan PNJ
    public function pnj($html, $dom)
    {
        if ($html) {

            $updated = $html->find('.style3', 1);
            $updated = str_replace('Cập nhật từ', '', $updated->plaintext);
            $updated = str_replace(' đến ', '_', $updated);
            $updated = explode('_', $updated)[0];

            $OilCron = DB::table('tygiavang_cron')->where('slug', '=', 'pnj')->orderBy('id', 'DESC')->first();

            $insert_id = $this->insertCron($updated, 'btmc.vn/api-get-list.htm', 'pnj');

            $donvi = 'Nghìn đồng / Lượng';
            $_SESSION['tinhthanh'] = '';
            $trs = $html->find("#column-2 .portlet-body table tbody tr");
            foreach ($trs as $tr) {
                $tds = $dom->str_get_html($tr->innertext);
                $td = $tds->find('td');

                if (count($td) == 5) {
                    $_SESSION['tinhthanh'] = ($td[0]->plaintext != '') ?  $td[0]->plaintext : $_SESSION['tinhthanh'];
                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $td[1]->plaintext;
                    $data['slug'] = "pnj";
                    $data['loai'] = $td[1]->plaintext;
                    $data['tinhthanh'] = $_SESSION['tinhthanh'];
                    $data['mua']  = str_replace('.', '', $td[2]->plaintext) / 10;
                    $data['ban'] = str_replace('.', '', $td[3]->plaintext) / 10;
                    $data['data'] = '';
                    $data['donvi'] = $donvi;
                    $this->insertVang($data, $OilCron);
                }
                if (count($td) == 4) {

                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $td[0]->plaintext;
                    $data['slug'] = "pnj";
                    $data['loai'] = $td[0]->plaintext;
                    $data['tinhthanh'] = $_SESSION['tinhthanh'];
                    $data['mua']  = str_replace('.', '', $td[1]->plaintext) / 10;
                    $data['ban'] = str_replace('.', '', $td[2]->plaintext) / 10;
                    $data['data'] = '';
                    $data['donvi'] = $donvi;
                    $this->insertVang($data, $OilCron);
                }
            }
            return 'Pnj Updated';
        }

        return 'html null';
    }
    public function phuquy($html, $dom)
    {

        if ($html) {
            $updated = $html->find('span.update-time', 0);
            $updated = str_replace('Cập nhập lúc: ', '', $updated->plaintext);

            $hanoi  = $html->find('table.goldprice-view tr', 0);
            $tbodys = $html->find('table.goldprice-view tbody tr');
            $OilCron = DB::table('tygiavang_cron')->where('slug', '=', 'phu-quy')->orderBy('id', 'DESC')->first();

            $insert_id = $this->insertCron($updated, 'gold.phuquy.com.vn', 'phu-quy');

            $_SESSION['row_loai'] = '';
            foreach ($tbodys as $index => $tbody) {
                $code = $dom->str_get_html($tbody->innertext);
                $code = $code->find('td');

                if (count($code) == 4) {
                    if ($code[0]->plaintext == 'Loại') continue;
                    if ($index == 0) $_SESSION['row_loai'] = 'Hà Nội';

                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $code[1]->plaintext;
                    $data['slug'] = "phu-quy";
                    $data['loai'] = $code[1]->plaintext;
                    $data['tinhthanh'] = $_SESSION['row_loai'];
                    $data['mua']  = str_replace('.', '', $code[2]->plaintext) / 1000;
                    $data['ban'] = str_replace('.', '', $code[3]->plaintext) / 1000;
                    $data['data'] = '';
                    $data['donvi'] = 'Đồng/Chỉ';
                    $this->insertVang($data, $OilCron);
                }
                if (count($code) == 1) {
                    $_SESSION['row_loai'] = $code[0]->plaintext;
                }
                if (count($code) == 3) {
                    $data['cron_id'] = $insert_id;
                    $data['tieude'] = $code[0]->plaintext;
                    $data['slug'] = "pnj";
                    $data['loai'] = $code[0]->plaintext;
                    $data['tinhthanh'] = $_SESSION['row_loai'];
                    $data['mua']  = str_replace('.', '', $code[1]->plaintext) / 1000;
                    $data['ban'] = str_replace('.', '', $code[2]->plaintext) / 1000;
                    $data['data'] = '';
                    $data['donvi'] = 'Đồng/Chỉ';
                    $this->insertVang($data, $OilCron);
                }
            }
            return 'Phuquy updated!';
        }
        return 'Html null!';
    }



    public function doji($html, $dom)
    {

        if ($html) {
            $updated = $html->find('span.update-time', 0);
            $updated = str_replace('Cập nhập lúc: ', '', $updated->plaintext);

            $OilCron = DB::table('tygiavang_cron')->where('slug', '=', 'doji')->orderBy('id', 'DESC')->first();
            // Doji thi insert o day luon

            $insert_id = $this->insertCron($updated, 'giavang.doji.com.vn', 'doji');

            $listVang = [1, 109, 108, 666, 670];
            foreach ($listVang as $id) {
                $m1 = $dom->file_get_html('http://giavang.doji.vn/sites/default/files/data/hienthi/vungmien_' . $id . '.dat');
                $tinhthanh = $m1->find('.title_bang', 0);
                $tinhthanh = $tinhthanh->plaintext;
                $trs = $m1->find('.goldprice-view tbody tr');
                foreach ($trs as $td) {
                    //$node = $td->
                    $node =  str_replace(' class="label"', '', $td->innertext);
                    $node =  str_replace('</td>', '', $node);
                    $node = explode('<td>', $node);
                    $oidData = DB::table('tygiavang_data')->where('slug', '=', 'doji')->where('cron_id', '=', $OilCron->id)->get();
                    foreach ($oidData as $value) {
                        if ($value->loai === $node[1] && $value->tinhthanh === $tinhthanh) {
                            if ((float) $node[2] > 0 || (float) $node[2] != null) {
                                $tyle_mua = 100 - (($value->mua / (float) $node[2]) * 100);
                            } else {
                                $tyle_mua = 0;
                            }
                            if ((float) $node[3] > 0 || (float) $node[3] != null) {
                                $tyle_ban = 100 - (($value->ban / (float) $node[3]) * 100);
                            } else {
                                $tyle_ban = 0;
                            }
                        } else {
                            continue;
                        }
                    }
                    DB::table('tygiavang_data')->insert([
                        'cron_id' => $insert_id,
                        'tieude'    => $tinhthanh,
                        'slug' => "doji",
                        'loai'    => $node[1],
                        'tinhthanh' => $tinhthanh,
                        'mua'  => (float) $node[2],
                        'tyle_mua' => (float) $tyle_mua,
                        'ban'  => (float) $node[3],
                        'tyle_ban' => (float) $tyle_ban,
                        'data' => '',
                        'donvi' => 'Nghìn/Lượng'
                    ]);
                }
            }

            return 'Doiji updated!';
        }
        return 'html null';
    }

    public function sjc()
    {
        $url = 'http://sjc.com.vn/xml/tygiavang.xml';
        $xml = simplexml_load_file($url);

        $ratelist = $xml->ratelist;

        // SJC cung insert o day luon


        $OilCron = DB::table('tygiavang_cron')->where('slug', '=', 'sjc')->orderBy('id', 'DESC')->first();

        $citys = $xml->ratelist->city;

        $insert_id = $this->insertCron($ratelist['updated'], 'sjc.com.vn', 'sjc');

        foreach ($citys as $city) {

            foreach ($city->item as $item) {
                $oidData = DB::table('tygiavang_data')->where('slug', '=', 'sjc')->where('cron_id', '=', $OilCron->id)->count();
                if ($oidData > 0) {
                    $tyle_mua = 0;
                    $tyle_ban = 0;
                    $oidData = DB::table('tygiavang_data')->where('slug', '=', 'sjc')->where('cron_id', '=', $OilCron->id)->get();

                    foreach ($oidData as $value) {

                        if ($value->loai === $item['type'] && $value->tinhthanh === $city['name']) {
                            if ($item['buy'] != null || $item['buy'] > 0) {
                                $tyle_mua = 100 - (($value->mua / ($item['buy'] * 100)) * 100);
                            }
                            if ($item['sell'] != null || $item['sell'] > 0) {
                                $tyle_ban = 100 - (($value->ban / ($item['sell'] * 100)) * 100);
                            }
                        } else {
                            continue;
                        }
                    }
                } else {
                    $tyle_mua = 0;
                    $tyle_ban = 0;
                }

                $buy = floatval($item['buy']);
                $sell = floatval($item['sell']);
                $sjcInsert = DB::table('tygiavang_data')->insert([
                    'cron_id' => $insert_id,
                    'tieude'    => $city['name'],
                    'slug' => 'sjc',
                    'loai'    => $item['type'],
                    'tinhthanh' => $city['name'],
                    'mua'  => $buy * 100,
                    'tyle_mua' => $tyle_mua,
                    'ban'  => $sell * 100,
                    'tyle_ban' => $tyle_ban,
                    'data' => '',
                    'donvi' => 'đồng/lượng'
                ]);
                if ($sjcInsert) {
                    echo "Update SJC success";
                } else {
                    echo "Update SJC failed";
                }
            }
        }

        echo " Updated SJC! \n ";
    }

    /* =============================== WEBGIA =============================== */
    //WEBGIA
    public function phuquy_webgia($html, $dom)
    {

        if ($html) {
            $updated = $html->find('h1.h-head small', 0);
            $updated = str_replace('- Cập nhật lúc ', '', $updated->plaintext);

            $check = DB::table('tygiavang_cron')
                ->where('cron_key', $updated)
                ->where('nguon', 'webgia.com/gia-vang/phu-quy')
                ->first();
            if (!$check) {
                $insert_id = $this->insertCron($updated, 'webgia.com/gia-vang/phu-quy', 'phuquy_webgia');
                $table = $html->find(".table-responsive table tbody tr");
                foreach ($table as $tr) {

                    $tds = $dom->str_get_html($tr->innertext);
                    $count = count($tds->find('td'));
                    if ($count == 4) {
                        $title = $tds->find('td', 0);
                        $_SESSION['current_title'] = $title->plaintext;
                        $loai = $tds->find('td', 1);
                        $mua = $tds->find('td', 2);
                        $ban = $tds->find('td', 3);
                    } else {
                        $loai = $tds->find('td', 0);
                        $mua = $tds->find('td', 1);
                        $ban = $tds->find('td', 2);
                    }

                    DB::table('tygiavang_data')->insert([
                        'cron_id' => $insert_id,
                        'tieude'  => isset($_SESSION['current_title']) ? $_SESSION['current_title'] : '',
                        'slug' => 'phu_quy_webgia',
                        'loai'    => $loai->plaintext,
                        'tinhthanh' => $loai->plaintext,
                        'mua'  => (float) $mua->plaintext,
                        'ban'  => (float) $ban->plaintext,
                        'data' => ''
                    ]);
                }
            }
            return 'PhuQuy updated!';
        }
        return 'Html Null';
    }

    public function pnj_webgia($html, $dom)
    {

        if ($html) {
            $updated = $html->find('h1.h-head small', 0);
            $updated = str_replace('- Cập nhật lúc ', '', $updated->plaintext);

            $check = DB::table('tygiavang_cron')
                ->where('cron_key', $updated)
                ->where('nguon', 'webgia.com/gia-vang/pnj')
                ->first();
            if (!$check) {
                $insert_id = $this->insertCron($updated, 'webgia.com/gia-vang/pnj', 'pnj_webgia');
                $table = $html->find(".table-responsive table tbody tr");
                foreach ($table as $tr) {

                    $tds = $dom->str_get_html($tr->innertext);
                    $count = count($tds->find('td'));
                    if ($count >= 4) {
                        $title = $tds->find('td', 0);
                        $_SESSION['current_title'] = $title->plaintext;
                        $loai = $tds->find('td', 1);
                        $mua = $tds->find('td', 2);
                        $ban = $tds->find('td', 3);
                    } else {
                        $loai = $tds->find('td', 0);
                        $mua = $tds->find('td', 1);
                        $ban = $tds->find('td', 2);
                    }

                    DB::table('tygiavang_data')->insert([
                        'cron_id' => $insert_id,
                        'tieude'  => isset($_SESSION['current_title']) ? $_SESSION['current_title'] : '',
                        'slug' => 'pnj_webgia',
                        'loai'    => $loai->plaintext,
                        'tinhthanh' => $loai->plaintext,
                        'mua'  => (float) $mua->plaintext,
                        'ban'  => (float) $ban->plaintext,
                        'data' => ''
                    ]);
                }
            }
            return 'Pnj updated!';
        }
        return 'Html Null';
    }

    public function btmc_webgia($html, $dom)
    {

        if ($html) {
            $updated = $html->find('h1.h-head small', 0);
            $updated = str_replace('- Cập nhật lúc ', '', $updated->plaintext);

            $check = DB::table('tygiavang_cron')
                ->where('cron_key', $updated)
                ->where('nguon', 'webgia.com/gia-vang/bao-tin-minh-chau')
                ->first();
            if (!$check) {
                $insert_id = $this->insertCron($updated, 'webgia.com/gia-vang/bao-tin-minh-chau', 'minhchau_webgia');
                $table = $html->find(".table-responsive table tbody tr");
                foreach ($table as $tr) {

                    $tds = $dom->str_get_html($tr->innertext);
                    $count = count($tds->find('td'));
                    if ($count == 4) {
                        $title = $tds->find('td', 0);
                        $_SESSION['current_title'] = $title->plaintext;
                        $loai = $tds->find('td', 1);
                        $mua = $tds->find('td', 2);
                        $ban = $tds->find('td', 3);
                    } else {
                        $loai = $tds->find('td', 0);
                        $mua = $tds->find('td', 1);
                        $ban = $tds->find('td', 2);
                    }

                    DB::table('tygiavang_data')->insert([
                        'cron_id' => $insert_id,
                        'tieude'  => isset($_SESSION['current_title']) ? $_SESSION['current_title'] : '',
                        'slug' => 'btmc_webgia',
                        'loai'    => $loai->plaintext,
                        'tinhthanh' => $loai->plaintext,
                        'mua'  => (float) $mua->plaintext,
                        'ban'  => (float) $ban->plaintext,
                        'data' => ''
                    ]);
                }
            }
            return 'btmc updated!';
        }
        return 'Html Null';
    }


    protected function sjcEximbank($html, $dom)
    {

        if ($html) {
            $mua = $html->find('span#GoldRateRepeater_lblCSHBUYRT_0', 0);
            $ban = $html->find('span#GoldRateRepeater_lblCSHSELLRT_0', 0);

            $insert_id = $this->insertCron("SJCEximbank", 'eximbank.com.vn', 'SJCEximbank');

            $insertDB = DB::table('tygiavang_data')->insert([
                'cron_id' => $insert_id,
                'tieude' => 'EximBank',
                'slug' => 'eximbank',
                'loai' => 'sjc',
                'tinhthanh' => 'bank',
                'mua' => str_replace(',', '', strip_tags($mua)),
                'tyle_mua' => null,
                'ban' => str_replace(',', '', strip_tags($ban)),
                'tyle_ban' => null,
                'data' => ' ',
                'donvi' => 'VND/chỉ'
            ]);
            if ($insertDB) {
                echo "success \n ";
            } else {
                echo "Success SJC Eximbank \n ";
            }
        }
    }

    protected function sjcVietinbank($html, $dom)
    {

        if ($html) {
            $container = $html->find('div.container', 5);

            $mua = $container->find('div.num_price', 0);
            $ban = $container->find('div.num_price', 1);

            $insert_id = $this->insertCron("SJCVietinBank", 'vietinbankgold.vn/gia-vang.html', 'SJCVietinBank');

            $insertDB = DB::table('tygiavang_data')->insert([
                'cron_id' => $insert_id,
                'tieude' => 'VietinBank',
                'slug' => 'vietin',
                'loai' => 'sjc',
                'tinhthanh' => 'bank',
                'mua' => floatval(str_replace(',', '', strip_tags($mua))),
                'tyle_mua' => null,
                'ban' => floatval(str_replace(',', '', strip_tags($ban))),
                'tyle_ban' => null,
                'data' => ' ',
                'donvi' => 'VND/lượng'
            ]);
            if ($insertDB) {
                echo "success SJC VietinBank \n ";
            } else {
                echo "Error SJC VietinBank \n ";
            }
        }
    }

    protected function sjcTechcombank($html, $dom)
    {

        if ($html) {
            $table = $html->find('table', 1);
            $tr = $table->find('tr', 1);
            $mua = $tr->find('td', 1);
            $ban = $tr->find('td', 2);
            $insert_id = $this->insertCron("SJCTechcombank", 'techcombank.com.vn', 'SJCTechcombank');

            $insertDB = DB::table('tygiavang_data')->insert([
                'cron_id' => $insert_id,
                'tieude' => 'TechcomBank',
                'slug' => 'techcom',
                'loai' => 'sjc',
                'tinhthanh' => 'bank',
                'mua' => str_replace(',', '', str_replace('&nbsp;', '', strip_tags($mua))),
                'tyle_mua' => null,
                'ban' => str_replace(',', '', str_replace('&nbsp;', '', strip_tags($ban))),
                'tyle_ban' => null,
                'data' => ' ',
                'donvi' => 'VND/chỉ'
            ]);
            if ($insertDB) {
                echo "success SJC Techcombank \n ";
            } else {
                echo "Errors SJC \n ";
            }
        }
    }

    protected function sjcSacombank($html, $dom)
    {

        if ($html) {
            $table = $html->find('div#tnbGoldPrice', 0);
            $tr = $table->find('tr', 1);

            $mua = $tr->find('td', 1);
            $ban = $tr->find('td', 2);

            $insert_id = $this->insertCron("SJCSacombank", 'sacombank.com.vn', 'SJCSacombank');

            $insertDB = DB::table('tygiavang_data')->insert([
                'cron_id' => $insert_id,
                'tieude' => 'SacomBank',
                'slug' => 'sacombank',
                'loai' => 'sjc',
                'tinhthanh' => 'bank',
                'mua' => str_replace('.', '', str_replace('&nbsp;', '', strip_tags($mua))),
                'tyle_mua' => null,
                'ban' => str_replace('.', '', str_replace('&nbsp;', '', strip_tags($ban))),
                'tyle_ban' => null,
                'data' => ' ',
                'donvi' => 'VND/chỉ'
            ]);
            if ($insertDB) {
                echo "success SJC Sacombank \n ";
            } else {
                echo "Error SJC Sacombankn \n ";
            }
        }
    }

    protected function giavangtg($html, $dom)
    {

        if ($html) {
            $table = $html->find('div#live-gold-quotes table', 0);
            $tr = $table->find('tr', 1);
            $mua = $tr->find('td', 0);
            $ban = $tr->find('td', 1);
            $tylemua = str_replace('+', '', strip_tags($tr->find('td', 3)));
            echo $tylemua;
            $insert_id = $this->insertCron("GiaVangTG", 'kitco.com', 'GiaVangTheGioi');

            $insertGiaVang = DB::table('tygiavang_data')->insert([
                'cron_id' => $insert_id,
                'tieude' => 'kitco',
                'slug' => 'thegioi',
                'loai' => 'sjc',
                'tinhthanh' => 'thegioi',
                'mua' => str_replace(',', '', str_replace('&nbsp;', '', strip_tags($mua))),
                'tyle_mua' => $tylemua,
                'ban' => str_replace(',', '', str_replace('&nbsp;', '', strip_tags($ban))),
                'tyle_ban' => null,
                'data' => ' ',
                'donvi' => 'USD/chỉ'
            ]);
            if ($insertGiaVang) {
                echo "success GiaVang TheGioi \n ";
            } else {
                echo "Errors GiaVang theGioi \n ";
            }
        }
    }
}
