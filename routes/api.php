<?php

use Illuminate\Http\Request;
$namespace = 'Api';
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('login','UserController@login');
Route::post('register','UserController@register');
Route::group(['middleware'=>'auth:api'], function(){
    Route::post('details','UserController@details');
});

Route::group(['prefix'=>'v1', 'namespace'=>$namespace], function() {
    //
    Route::get('get-exchange', 'ExchangeController@index');
    Route::get('get-exchange/{bank_code}', 'ExchangeController@show');

    Route::get('get-currency', 'ExchangeController@getCurrency');
    Route::get('get-currency/{currency}', 'ExchangeController@edit');

    Route::get('get-gold-exchange', 'GoldController@index');
    Route::get('get-gold-exchange/{currency}', 'GoldController@show');
    Route::get('get-gold-exchange-world', 'GoldController@world');

    Route::get('get-interest-rate', 'InterestRateController@index');

    Route::get('get-virtual-money', 'VirtrualMoneyRateController@index');
    Route::get('get-virtual-money/{slug}', 'VirtrualMoneyRateController@show');
});
