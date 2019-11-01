<?php

namespace App\Http\Controllers\Api;

use App\Models\LoaiTienAo;
use App\Models\TienAo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VirtrualMoneyRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $arrLoaiTienAo = array(1, 2, 3, 8, 9, 4, 13, 10, 11, 14);
        $tienAo = LoaiTienAo::whereIn('id', $arrLoaiTienAo)->select('id','name','slug','symbol')->get();
        foreach ($tienAo as $value) {
            $virtualMoney = TienAo::where('slug', $value->slug)->orderBy('id', 'DESC')->first();
            if (!$virtualMoney) {
                continue;
            }
            $value->circulating_supply = $virtualMoney->circulating_supply;
            $value->total_supply = $virtualMoney->total_supply;
            $value->max_supply = $virtualMoney->max_supply;
            $value->num_market_pairs = $virtualMoney->num_market_pairs;
            $value->price = $virtualMoney->price;
            $value->volume_24h = $virtualMoney->volume_24h;
            $value->percent_change_1h = $virtualMoney->percent_change_1h;
            $value->percent_change_24h = $virtualMoney->percent_change_24h;
            $value->percent_change_7d = $virtualMoney->percent_change_7d;
            $value->market_cap = $virtualMoney->market_cap;
        }

        return response()->json($tienAo, 200);
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
     * @param  int  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $virtualMoneyUSD = TienAo::where('slug', $slug)->where('currency_type','USD')->orderBy('id', 'DESC')->first();
        if (!$virtualMoneyUSD) {
            return response()->json(["message" => "Money not found"], 403);
        }
        $virtualMoneyVND = TienAo::where('slug', $slug)->where('currency_type','VND')->orderBy('id', 'DESC')->first();
        if (!$virtualMoneyVND) {
            return response()->json(["message" => "Money not found"], 403);
        }
        $virtualMoneyUSD->vnd = $virtualMoneyVND->price;

        return response()->json($virtualMoneyUSD, 200);
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
