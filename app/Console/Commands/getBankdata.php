<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use App\Modules\Exchanges\Helpers\ExchangesHelper;
use App\Modules\Exchanges\Models\NgoaiTe;
use App\Modules\Exchanges\Models\NgoaiTeCron;
use Carbon\Carbon;
use App\Helpers\SimpleHtmlDom;
class getBankdata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getBankdata';

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
        $NgoaiTeCron = new NgoaiTeCron();
        $Carbon = Carbon::now();
        $NgoaiTeCron->cronkey = str_random(5). "_" . $Carbon->toDateString();
        $NgoaiTeCron->save();
        $SimpleHTMLDOM = new SimpleHtmlDom();
        $path = app_path('Modules//Exchanges//Providers');
        $listProvider = array_map('basename', File::directories($path));
        foreach ($listProvider as $key => $bank){
            try{
                if (file_exists($path . '/' . $bank . '/' . $bank . '.php')) {
                    $classPath = '\App\Modules\Exchanges\Providers\\' . $bank . '\\' . $bank;
                    $ProviderN = new $classPath;
                    $inser = $ProviderN->getExchange($NgoaiTeCron->id,$SimpleHTMLDOM);
                }
            }catch (\Exception $ex){
            }
        }
    }
}
