<?php

namespace App\Console\Commands;

use App\Modules\Exchanges\Models\BankInfo;
use App\Modules\Exchanges\Models\LoaiTienAo;
use Illuminate\Console\Command;
use Carbon\Carbon;
use File;
use DB;

class CreateSiteMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:create';

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
        // Home
        $sitemap = \App::make('sitemap');
        // add home pages mặc định
        $sitemap->add(url('/'), Carbon::today()->toAtomString(), '1.0', 'always');
        $Carbon = Carbon::now();
        /**
         * site map level 2
         * */
        $sitemap->add(url('/gia-vang'), Carbon::today()->toAtomString(), '0.9', 'always');
        $sitemap->add(url('/ngoai-te'), Carbon::today()->toAtomString(), '0.9', 'always');
        $sitemap->add(url('/lai-suat'), Carbon::today()->toAtomString(), '0.9', 'always');
        $sitemap->add(url('/tien-ao'), Carbon::today()->toAtomString(), '0.9', 'always');
        $sitemap->add(url('/xang-dau'), Carbon::today()->toAtomString(), '0.9', 'always');
        $sitemap->add(url('/blog/kien-thuc-chung-725'), Carbon::today()->toAtomString(), '0.9', 'always');
        /**
         * site map level 3
         * Bao gồm các site ở trên thanh menu
         * */
        $BankInfo = BankInfo::where('active','=',1)->get();
        if($BankInfo){
            foreach ($BankInfo as $tygia) {
                $sitemap->add(url('/ty-gia', [$tygia->bankcode]), $tygia->created_at, '0.8', 'always');
            }
        }
        $sitemap->add(url('/lai-suat',['ls_ocb']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_mb']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_scb']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_shb']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_vietcombank']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_dab']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_vietin']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_bidv']), $Carbon,'0.8','always');
        $sitemap->add(url('/lai-suat',['ls_msb']), $Carbon,'0.8','always');

        $sitemap->add(url('/ty-gia/bieu-do'), $tygia->created_at, '0.8', 'always');
        $NhaPhanPhoi = DB::table('tygiavang_distributor')
            ->select('slug','created_at')
            ->get();
        if($NhaPhanPhoi){
            foreach($NhaPhanPhoi as $value){
                $sitemap->add(url('/gia-vang',[$value->slug]) , $value->created_at, '0.8','always');
            }
        }
        $sitemap->add(url('/gia-vang/the-gioi') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-sjc-hom-nay-2309') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-sjc-3-ngay-2312') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-sjc-7-ngay-2313') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-sjc-1-thang-2310') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-gia-vang-sjc-3-thang-2314') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-gia-vang-sjc-6-thang-2315') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang/bieu-do/bieu-do-sjc-1-nam-2311') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang-bank/sjc_eximbank') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang-bank/sjc_sacombank') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang-bank/sjc_techcom') , $value->created_at, '0.8','always');
        $sitemap->add(url('/gia-vang-bank/sjc_vietin') , $value->created_at, '0.8','always');

        // Ngoai te
        $sitemap->add(url('/ngoai-te/eur'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/krw'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/thb'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/sgd'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/dkk'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/rub'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/twd'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/gdb'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/hkd'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/aud'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/sek'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/nok'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/nzd'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/usd'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/jpy'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/chf'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/cad'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/lak'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/cny'), $Carbon,'0.8','always');
        $sitemap->add(url('/ngoai-te/myr'), $Carbon,'0.8','always');


        // Tien ao
        $sitemap->add(url('/tien-ao/bitcoin'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/bitcoin-cash'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/cardano'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/iota'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/tron'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/vechain'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/qtum'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/ethereum'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/litecoin'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/stellar'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/monero'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/nem'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/ethereum-classic'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/omisego'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/ripple'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/eos'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/neo'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/dash'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/tether'),$Carbon , '0.8', 'always');
        $sitemap->add(url('/tien-ao/binance-coin'),$Carbon , '0.8', 'always');

        $Xangdau = DB::table('xangdau')
            ->where('ten','!=',null)
            ->where('slug','!=',null)
            ->orderBy('id','DESC')
            ->select('slug','created_at')
            ->first();
        if($Xangdau){
            $sitemap->add(url('/gia-xang-dau',[$Xangdau->slug]), $Xangdau->created_at,'0.8', ' always');
        }
        $sitemap->add(url('/gia-xang-dau'), $Xangdau->created_at,'0.8', ' always');

        $News = DB::table('news')
            ->where('news_slug','!=',null)
            ->orderBy('id','DESC')
            ->select('id','news_slug','created_at')
            ->get();
        if($News){
            foreach($News as $news){
                $sitemap->add(url('/chi-tiet',[$news->news_slug]), $news->created_at,'0.8','always');
            }
        }

        /**
         * site map level 4
         * Bao gồm các trang tìm kiếm, lọc dữ liệu v.v...
         * */
//        if($NhaPhanPhoi){
//            foreach ($NhaPhanPhoi as $nhaphanphoi){
//                $sitemap->add(url('/gia-vang',[$nhaphanphoi->slug],'/fillter'), $nhaphanphoi->created_at,'0.7','always');
//            }
//        }
//        $sitemap->add(url('/tra-cuu'), Carbon::now(),'0.7','always');




        // lưu file và phân quyền
        $sitemap->store('xml', 'sitemap');
//        if (File::exists('../sitemap.xml')) {
//            chmod('../sitemap.xml', 0777);
//        }
    }
}
