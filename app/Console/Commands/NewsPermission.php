<?php

namespace App\Console\Commands;

use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Illuminate\Console\Command;

class NewsPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:news_permission';

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
        $SiteSettings = SiteSettings::all();
        $RSSNews_all = RSSNews::all();
        // dd($RSSNews_all[0]->get_cate[0]->get_cate_name->name);

        $news_array = array();
        // $new_array_sub = [];
        if($RSSNews_all) {
            foreach($RSSNews_all as $key => $val) {
                $val_news_id = $val->id;
                // $val->get_cate[0]->get_cate_name->id;
                $new_array_sub = [];
                if($val->get_cate) {
                    foreach($val->get_cate as $key2 => $val2) {
               
                        $new_array_sub['news_id'] = $val_news_id;
                        $new_array_sub['cate_id'] = $val2->id;
       
                    }
                    array_push($news_array, $new_array_sub);
                }

            }
        }

        $site_array = [];
        if($SiteSettings) {
            foreach($SiteSettings as $key => $val) {
                $val_news_id = $val->id;
                // $val->get_cate[0]->get_cate_name->id;
                $site_array_sub = [];
                if($val->get_categorys) {
                    foreach($val->get_categorys as $key2 => $val2) {

               
                        $site_array_sub['site_id'] = $val_news_id;
                        $site_array_sub['cate_id'] = $val2->id;

                    }
                    array_push($site_array, $site_array_sub);
                }

            }
        }



        $this->info('Save news persission successfully');
    }
}
