<?php

namespace App\Http\Controllers\Api;

use App\Models\BankInfo;
use App\Models\NgoaiTe;
use App\Models\NgoaiTeCron;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Exchanges\ExchangeRepositoryInterface;
use App\Helpers\ResponseAPI;

class ExchangeController extends Controller
{
    private $exchange;
    private $responseAPI;
    public function __construct(ExchangeRepositoryInterface $exchange)
    {
        $this->exchange = $exchange;
        $this->responseAPI = new ResponseAPI();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bankInfo = BankInfo::where('active', 1)->orderBy('sort', 'ASC')->get();
        if (!$bankInfo) {
            return response()->json(["message" => "Cannot find the bank"], 422);
        }

        $ngoaiTeCron = NgoaiTeCron::where('cronkey','!=', null)->orderBy('id', 'DESC')->first();
        if (!$ngoaiTeCron) {
            return response()->json(["message" => "Cannot find exchanges cron jobs"], 403);
        }
        $ngoaiTe = NgoaiTe::where('cron_id', $ngoaiTeCron->id)->where('default', 0)->orderBy('id', 'DESC')
            ->select(
                'id','code','bank_id',
                'bank_code', 'time',
                'muatienmat','muatienmat_diff','bantienmat','bantienmat_diff',
                'muachuyenkhoan','muachuyenkhoan_diff','banchuyenkhoan','banchuyenkhoan_diff'
            )->get();
        if (!$ngoaiTe) {
            return response($this->responseAPI->responseAPI(array()));
        }
        $mergeData = $this->exchange->mergeExchange($bankInfo, $ngoaiTe);
        if (!$mergeData) {
            return response()->json(["message" => "Data errors"], 422);
        }

        return response()->json($mergeData, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $bankCode
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $bankCode)
    {
        $bankInfo = BankInfo::where('bankcode', $bankCode)->where('active', 1)->first();
        if (!$bankInfo) {
            return response()->json(["message" => "Cannot find the bank"], 403);
        }
        $ngoaiTeCron = NgoaiTeCron::where('cronkey','!=', null)->orderBy('id', 'DESC')->first();
        $ngoaiTe = NgoaiTe::where('cron_id', $ngoaiTeCron->id)
            ->where('bank_code', $bankInfo->bankcode)->where('default', 0)->orderBy('id', 'DESC')
            ->select(
                'id','code','bank_id',
                'bank_code', 'time',
                'muatienmat','muatienmat_diff','bantienmat','bantienmat_diff',
                'muachuyenkhoan','muachuyenkhoan_diff','banchuyenkhoan','banchuyenkhoan_diff'
            )->get();
        if (!$ngoaiTe) {
            return response()->json(["message" => "Currency not found"], 403);
        }
        $mergeData = $this->exchange->mergeExchangeOfBank($bankInfo, $ngoaiTe);

        return response()->json($mergeData, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $currency
     * @return \Illuminate\Http\Response
     */
    public function edit($currency)
    {
        $cron = NgoaiTeCron::where('cronkey', '!=', null)->orderBy('id', 'DESC')->first();
        if (!$cron) {
            return response()->json(["message" => "cannot find cron job"], 403);
        }
        $exchanges = NgoaiTe::where('cron_id', $cron->id)->where('default', 0)
            ->where('code', $currency)
            ->orderBy('id', 'DESC')->select( 'id',
                'code','bank_id','bank_code',
                'symbol','ename','vname',
                'muatienmat','muatienmat_diff','bantienmat','bantienmat_diff',
                'muachuyenkhoan','muachuyenkhoan_diff','banchuyenkhoan','banchuyenkhoan_diff'
            )->get();
        if (!$exchanges) {
            return response()->json(["message" => "Data not found"], 403);
        }

        return response()->json($exchanges, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $currency
     * @return \Illuminate\Http\Response
     */
    public function getCurrency()
    {
        $exchangeCron = NgoaiTeCron::orderBy('id', 'DESC')->first();
        if (!$exchangeCron) {
            return response()->json(["message" => "Cannot find cron job"], 403);
        }

        $exchanges = NgoaiTe::where('cron_id', $exchangeCron->id)->where('default', 0)
            ->where('code', 'USD')
            ->orderBy('id', 'DESC')->select( 'id',
                'code','bank_id','bank_code',
                'symbol','ename','vname',
                'muatienmat','muatienmat_diff','bantienmat','bantienmat_diff',
                'muachuyenkhoan','muachuyenkhoan_diff','banchuyenkhoan','banchuyenkhoan_diff'
            )->get();
        if (!$exchanges) {
            return response()->json(["message" => "Data not found"], 403);
        }

        return response()->json($exchanges, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
