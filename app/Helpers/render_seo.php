<?php
/**
 * Created by PhpStorm.
 * User: nguyenlongit95
 * Date: 4/4/19
 * Time: 6:35 PM
 */
namespace App\Helpers;
class render_seo{
    public function render_seo($blade){
        $seo = \App\Modules\Seo\Models\Seo::getMeta();
        return theme_view('widgets.'.$blade, compact('seo'))->render();
    }
}
?>
