<?php

namespace App\Http\Controllers\Api;

use App\Models\BankInfo;
use App\Models\LaiSuat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InterestRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $arrListBank = array(8, 1, 11, 7, 13, 9, 3, 5);
        $bankInfo = BankInfo::where('active', 1)->orderBy('sort', 'ASC')->pluck('id');
        if (!$bankInfo) {
            return response()->json(["message" => "Cannot find the bank"], 403);
        }
        $arrKyHanSlug = array(0,1,3,6,9,12,24,36);
        $laiSuat = LaiSuat::whereIn('bank_id', $arrListBank)->whereIn('kyhanslug', $arrKyHanSlug)
            ->orderBy('id', 'DESC')->take(64)->get();
        if (!$laiSuat) {
            return response()->json(["message" => "Cannot find data"], 403);
        }

        return response()->json($laiSuat, 200);
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
