<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\XangDau;
use App\Helpers\SimpleHtmlDom;
class getOilPetro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getOilPetro:getOilPetro';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get oil petrolimex';
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
        $SimpleHTMLDOM = new SimpleHtmlDom();
        $url = "https://www.petrolimex.com.vn/";
        try{
            $html = $SimpleHTMLDOM->file_get_html($url);
        }catch(\Exception $exception){
            $html = null;
        }
        if($html){
            $this->getOilPetrolimex($html);
        }
    }
    public function getOilPetrolimex($html){
        $rows = $html->find("div#vie_3_Right div#vie_p6_Container div#vie_p6_PortletContent div.list-table div");
        $arrXangDau = array();
        for($i = 3; $i < count($rows); $i+=2){
            array_push($arrXangDau, $rows[$i]);
        }
        for($j = 0; $j < count($arrXangDau); $j+=2){
            $XangDau = new XangDau();
            $XangDau->ten = strip_tags($arrXangDau[$j]->find("div",0));
            $XangDau->slug = "petrolimex";
            $XangDau->giavung1 = floatval(str_replace(',','.',strip_tags($arrXangDau[$j]->find("div",1))));
            $XangDau->giavung2 = floatval(str_replace(',','.',strip_tags($arrXangDau[$j]->find("div",2))));
            $XangDau->save();
        }
    }
}
