<?php

namespace App\Console\Commands;

use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteNewsRelatedTemp;
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
        $SiteNewsRelated = SiteNewsRelated::all();
        $SiteCategory = SiteCategory::all();

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
                        $new_array_sub['cate_id'] = $val2->news_category_id;
       
                    }
                    $new_array_sub_cal = array_filter($new_array_sub);
                    array_push($news_array, $new_array_sub_cal);
                }

            }
        }

        $site_array = [];
        // if($SiteSettings) {
        //     foreach($SiteSettings as $key => $val) {
                
        //         // $val->get_cate[0]->get_cate_name->id;
        //         // $site_array_sub = [];
        //         if($val) {
        //             $val_news_id = $val->id;
        //             if($val->get_categorys) {
        //                 foreach($val->get_categorys as $key2 => $val2) {
        //                     if($val2) {
        //                         $site_array_sub['site_id'] = $val_news_id;
        //                         $site_array_sub['cate_id'] = $val2->category_id;
        //                     }
        //                 }
        //                 $site_array_sub_cal = array_filter($site_array_sub);
        //                 array_push($site_array, $site_array_sub_cal);
        //             }
        //         }
        //     }
        // }

        if($SiteCategory) {
            foreach($SiteCategory as $key => $val) {
                
                // $val->get_cate[0]->get_cate_name->id;
                // $site_array_sub = [];
                if($val) {
                    $val_site_id = $val->site_id;
                    if($val->site_id) {
                        
                                $site_array_sub['site_id'] = $val_site_id;
                                $site_array_sub['cate_id'] = $val->category_id;
                            
                        $site_array_sub_cal = array_filter($site_array_sub);
                        array_push($site_array, $site_array_sub_cal);
                    }
                }
            }
        }
        

        //site_news_related จาก db
        $site_news_related = [];
        if($SiteNewsRelated) {
            foreach($SiteNewsRelated as $key => $val) {
                if($val) {
                    $val_news_related_id = $val->site_id;
                    $val_news_id = $val->news_id;
                    // $val->get_cate[0]->get_cate_name->id;
                    
                    if($val->get_news) {
                        $news_related_array_sub = [];
                        foreach(@$val->get_news as $key2 => $val2) {

                            if($val2) {
                                $news_related_array_sub['site_id'] = $val_news_related_id;
                                // $news_related_array_sub['cate_id'] = @$val2->news_category_id;
                                $news_related_array_sub['cate_id'] = @$val2->get_cate[0]->news_category_id;
                                $news_related_array_sub['news_id'] = $val_news_id;
                            }
    
                 
    
                        }
                        $news_related_array_sub_cal = array_filter($news_related_array_sub);
                        array_push($site_news_related, $news_related_array_sub_cal);
                    }
                }
            }
        }




        //site news
        $site_and_news_array_all = [];
        if($news_array) {
            foreach($news_array as $news_key => $news_val) {
                // dd($news_val['news_id']);
                if($news_val) {
                    if($site_array) {
                        $site_and_news_array = [];
                        foreach($site_array as $site_key => $site_val) {
                            if($site_val) {
                                if(@$news_val['cate_id'] == @$site_val['cate_id']) {
                                    if(@$site_val['cate_id'] && @$site_val['site_id'] && @$news_val['news_id'] && @$news_val['cate_id']) {

                                        
                                                $site_and_news_array['site_id'] = $site_val['site_id'];
                                                $site_and_news_array['cate_id'] = $site_val['cate_id'];
                                                $site_and_news_array['news_id'] = $news_val['news_id'];
                                        
                                        
                                    }
                  
    
                                }
                                
                            }
                        }
                        array_push($site_and_news_array_all, $site_and_news_array);
                    }
                }  
            }
        }




        // $site_news_related_insert_all = [];
        // $site_news_related_insert = [];
        // $site_news_related_insert_sub = [];
        // if($site_news_related) {
        //     foreach($site_news_related as $key => $val) {
        //         if($val) {

        //             if($site_and_news_array_all) {
        //                 foreach($site_and_news_array_all as $key2 => $val2) {
        //                     if(@$val['site_id'] == @$val2['site_id'] && @$val['cate_id'] == @$val2['cate_id'] && @$val['news_id'] == @$val2['news_id']) {
                                

        //                     } else {
        //                         $site_news_related_insert_sub['site_id'] = @$val2['site_id'];
        //                         $site_news_related_insert_sub['cate_id'] = @$val2['cate_id'];
        //                         $site_news_related_insert_sub['news_id'] = @$val2['news_id'];

        //                         // dd($site_news_related_insert_sub);

        //                         $site_news_related_insert_sub_cal = array_filter($site_news_related_insert_sub);
        //                         array_push($site_news_related_insert, $site_news_related_insert_sub_cal);
        //                     }

        //                 }
        //             }


        //         }
        //     }

        //     $site_news_related_insert_cal = array_filter($site_news_related_insert);
        //     array_push($site_news_related_insert_all, $site_news_related_insert_cal);


        // }

        SiteNewsRelatedTemp::truncate();
        if($site_and_news_array_all) {
            // dd($site_and_news_array_all);
            foreach($site_and_news_array_all as $key => $val) {
                // dd($val);
                if($val) {
                    $SiteNewsRelatedTemp = new SiteNewsRelatedTemp;
                    $SiteNewsRelatedTemp->site_id = @$val['site_id'];
                    // $SiteNewsRelatedTemp->cate_id = $val['cate_id'];
                    $SiteNewsRelatedTemp->news_id = @$val['news_id'];
                    $SiteNewsRelatedTemp->save();
                }
            }
        }

        $SiteNewsRelatedTemp = SiteNewsRelatedTemp::all();
        if($SiteNewsRelatedTemp) {
            if($site_and_news_array_all) {
                SiteNewsRelated::truncate();
                foreach($site_and_news_array_all as $key => $val) {
                    if($val) {
                        $SiteNewsRelatedTemp = new SiteNewsRelated;
                        $SiteNewsRelatedTemp->site_id = @$val['site_id'];
                        // $SiteNewsRelatedTemp->cate_id = $val['cate_id'];
                        $SiteNewsRelatedTemp->news_id = @$val['news_id'];
                        $SiteNewsRelatedTemp->save();
                    }
                }
            }
        }





        $this->info('Save news persission successfully');
    }
}
