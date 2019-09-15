<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Exchanges\Models\TienAo;
use App\Modules\Exchanges\Models\LoaiTienAo;
class getVirtualMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getVirtualMoney:VirtualMoney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get money house only';

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
        //
        $this->bitcoin();
    }

    public function bitcoin(){
        $CurrencyUSD = "USD";
        $CurrencyVND = "VND";
        $ApiKey = "395cfc82-a582-4171-8c2b-49e72e5d8f1f";
        try{
            $jsonDataUSD = $this->callAPIS($CurrencyUSD,1, 5000,$ApiKey);
            $jsonDataVND = $this->callAPIS($CurrencyVND,1, 5000, $ApiKey);
        }catch(\Exception $e){
            $jsonDataUSD = null;
            $jsonDataVND = null;
        }
        if($jsonDataUSD->status->error_code != 0){
            return response()->json(["message",$jsonDataUSD->status->error_message]);
        }
        /**
         * get USD
         * @currency_type is "USD"
         * */
        $i = 0;
        foreach($jsonDataUSD->data as $key=>$value){
            if($i < 27) {
                $TienAo = new TienAo();
                $TienAo->name = $value->name;
                $TienAo->symbol = $value->symbol;
                $TienAo->slug = $value->slug;
                $TienAo->circulating_supply = $value->circulating_supply;
                $TienAo->total_supply = $value->total_supply;
                $TienAo->max_supply = $value->max_supply;
//            $TienAo->date_added = $value->date_added;
                $TienAo->num_market_pairs = $value->num_market_pairs;
                $TienAo->rank = $value->cmc_rank;
//            $TienAo->last_updated = $value->last_updated;
                $TienAo->price = $value->quote->USD->price;
                $TienAo->volume_24h = $value->quote->USD->volume_24h;
                $TienAo->percent_change_1h = $value->quote->USD->percent_change_1h;
                $TienAo->percent_change_24h = $value->quote->USD->percent_change_24h;
                $TienAo->percent_change_7d = $value->quote->USD->percent_change_7d;
                $TienAo->market_cap = $value->quote->USD->market_cap;
                $TienAo->currency_type = $CurrencyUSD;
                $TienAo->save();
                $i++;
            }else{
                break;
            }
        }
        /**
         * get VND
         * */
        if($jsonDataVND->status->error_code != 0){
            return response()->json(["message",$jsonDataVND->status->error_message]);
        }
        $j = 0;
        foreach($jsonDataVND->data as $value){
            if($j < 27) {
                $TienAo = new TienAo();
                $TienAo->name = $value->name;
                $TienAo->symbol = $value->symbol;
                $TienAo->slug = $value->slug;
                $TienAo->circulating_supply = $value->circulating_supply;
                $TienAo->total_supply = $value->total_supply;
                $TienAo->max_supply = $value->max_supply;
//            $TienAo->date_added = $value->date_added;
                $TienAo->num_market_pairs = $value->num_market_pairs;
                $TienAo->rank = $value->cmc_rank;
//            $TienAo->last_updated = $value->last_updated;
                $TienAo->price = $value->quote->VND->price;
                $TienAo->volume_24h = $value->quote->VND->volume_24h;
                $TienAo->percent_change_1h = $value->quote->VND->percent_change_1h;
                $TienAo->percent_change_24h = $value->quote->VND->percent_change_24h;
                $TienAo->percent_change_7d = $value->quote->VND->percent_change_7d;
                $TienAo->market_cap = $value->quote->VND->market_cap;
                $TienAo->currency_type = $CurrencyVND;
                $TienAo->save();
                $j++;
            }else{
                break;
            }
        }
    }

    protected function callAPIS($Currency, $Start, $limit,$ApiKey){
        # This example requires curl is enabled in php.ini
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
        $parameters = [
            'start' => $Start,
            'limit' => $limit,
            'convert'=> $Currency,
        ];

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: '. $ApiKey
        ];
        $qs = http_build_query($parameters);
        $request = "{$url}?{$qs}"; // create the request URL

        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));
        $response = curl_exec($curl); // Send the request, save the response
        $jsonData = json_decode($response);
        curl_close($curl);
        return $jsonData;
    }
}
