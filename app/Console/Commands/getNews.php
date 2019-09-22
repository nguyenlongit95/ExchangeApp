<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Modules\News\Models\News;

use App\Helpers\ChangeText;

use App\Helpers\SimpleHtmlDom;
use DB;
use App\Seo;

class getNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getNews:NewsTyGia';

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
        $dom = new SimpleHtmlDom();
        //        $url = 'https://tygia.vn/blog/kien-thuc-chung-725';
        for ($i = 30; $i >= 1; $i--) {
            $url = 'https://tygia.vn/blog/kien-thuc-chung-725?page=' . $i;
            try {
                $html = $dom->file_get_html($url);
            } catch (\Exception $e) {
                $html = null;
            }
            $this->getNews($html, $dom, $url);
        }
    }

    protected function getNews($html, $dom, $url)
    {

        if ($html) {

            $Blog = $html->find('div#main article.blog-list');
            foreach ($Blog as $blog) {

                $Bloghref = $blog->find('a.blog-image', 0)->href;
                $getIDOid = array_reverse(explode('-', $Bloghref));
                $BlogImage = $blog->find('a.blog-image img', 0)->src;
                $dir = "./public/storage/userfiles/images/news/" . basename($BlogImage);
                try {
                    file_put_contents($dir, file_get_contents($BlogImage));
                } catch (\Exception $exception) {
                    continue;
                }
                $ChangeText = new ChangeText();
                $CallBlogDetails = $dom->file_get_html($Bloghref);
                $Details = $CallBlogDetails->find('article#main div.blog-content', 0);
                $BlogTitle = $CallBlogDetails->find('h1.h-head', 0)->innertext;
                $check = DB::table('news')
                    ->where('news_slug', '=', $ChangeText->changeTitle($BlogTitle) . "-" . $getIDOid[0])
                    ->count();
                if ($check > 0) {
                    continue;
                }
                $BlogSortDescription = $CallBlogDetails->find('article#main div.blog-content p', 2)->innertext;
                $changeImage = $dom->str_get_html($Details);
                $changeImage->find('img', 0)->src = "/storage/userfiles/images/news/" . basename($BlogImage);
                $changeimgDescription = $dom->str_get_html($BlogSortDescription);
                if ($changeimgDescription->find('img', 0)) {
                    $changeimgDescription->find('img', 0)->src = "/storage/userfiles/images/news/" . basename($BlogImage);
                } else {
                    $changeimgDescription = $BlogSortDescription;
                }
                //                $KeyWord = strip_tags($CallBlogDetails->find('article#main footer.blog-extra span',0)->innertext);

                $KeyWords = html_entity_decode($html->find("meta[name='keywords']", 0)->content);
                $Description = html_entity_decode($html->find("meta[name='description']", 0)->content);


                /**
                 * Thêm luôn vào bảng SEO ở đây
                 * */
                // Connect domain get title website
                $str = file_get_contents($Bloghref);
                $titleWeb = "";
                if (strlen($str) > 0) {
                    $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
                    preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title); // ignore case
                    $titleWeb = $title[1];
                }

                $News = new News();
                $News->title = $BlogTitle;
                $News->news_slug = $ChangeText->changeTitle($BlogTitle) . "-" . $getIDOid[0];
                $News->short_description = $changeimgDescription;
                $News->content = $changeImage;
                $News->author = "Adminstator";
                $News->author_email = "suport@nencer.com";
                $News->image = "/storage/userfiles/images/news/" . basename($BlogImage);

                $News->status = 1;

                if ($News->save()) {
                    $link = "/chi-tiet/" . $ChangeText->changeTitle($BlogTitle) . "-" . $getIDOid[0];
                    $Seo = new Seo();
                    $Seo->link = $link;
                    $Seo->checksum = md5($link);
                    $Seo->title = $titleWeb;
                    $Seo->keywords = $KeyWords;
                    $Seo->Description = $Description;
                    $Seo->h1 = $BlogTitle;
                    $Seo->noindex = 0;
                    $Seo->avatar = "/storage/userfiles/images/news/" . basename($BlogImage);
                    $Seo->language = "vi";
                    if ($Seo->save()) {
                        echo "Update Blog: " . $BlogTitle . " success. \n ";
                    }
                } else { }
            }
        }
    }
}
