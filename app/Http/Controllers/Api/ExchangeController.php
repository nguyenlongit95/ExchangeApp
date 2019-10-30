<?php

namespace App\Http\Controllers\Api;

use App\Models\BankInfo;
use App\Models\NgoaiTe;
use App\Models\NgoaiTeCron;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Exchanges\ExchangeRepositoryInterface;

class ExchangeController extends Controller
{
    private $exchange;

    public function __construct(ExchangeRepositoryInterface $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get Bank and Currency
        $bankInfo = BankInfo::where('active', 1)->orderBy('sort', 'ASC')->get();
        if (!$bankInfo) {
            return response()->json(["message" => "Cannot find the bank"], 422);
        }
        // Ngoai Te Cron
        $ngoaiTeCron = NgoaiTeCron::where('cronkey','!=', null)->orderBy('id', 'DESC')->first();

        $ngoaiTe = NgoaiTe::where('cron_id', $ngoaiTeCron->id)->where('default', 0)->orderBy('id', 'DESC')->get();
        if (!$ngoaiTe) {
            return response(["message" => "Data not found"], 403);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
