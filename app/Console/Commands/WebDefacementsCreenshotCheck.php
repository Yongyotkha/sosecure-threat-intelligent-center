<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WebDefacementsCreenshotCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:WebDefacementsCreenshotCheck {url} {port} {site_id} {url_id} {delay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementsCreenshotCheck';

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
        $url= $this->argument('url');
        $port=$this->argument('port');
        $site_id =$this->argument('site_id');
        $url_id = $this->argument('url_id');
        if ($url_id == 0) {
          $url_id =rand(10,100);
      }

      $delay = $this->argument('delay');
      $result = array();
    //   $result=new stdClass();
      if ($this->is_url($url)) {
        if (1== 1) {
            $result["Result"] = 1;

            // $result->Result = 1;
            $result["image_url"] = "/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
            $result["message"] = "";
            $path_include = base_path().'/public/screenshot/use/DownloadImage.php';
            include_once($path_include);
            $downloadImg = new \DownloadImage();
            $Path_image = base_path()."/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
            $downloadImg->download($url,$Path_image,$delay);
            $result["image_path_original_full"] = $Path_image;
            $result["image_path_original"] = "/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
            $result["url_id"] = $url_id;

        }else{
            $result["Result"] = 0;
            $result["message"] = "The website is not online.";
        }
    }else{
        $result["Result"] = 0;
        $result["message"] = "The url is not formatted.";
    }


    // var_dump($result);

    $result_json_e = json_encode($result);
    $this->output->write($result_json_e);



}
function is_url($uri){
    if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
      return $uri;
  }
  else{
    return false;
}
}


}
