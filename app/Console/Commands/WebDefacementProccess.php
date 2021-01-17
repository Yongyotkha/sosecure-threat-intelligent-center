<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentImageMark;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataLog;
use App\Entities\TransactionBatchjob;

class WebDefacementProccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:WebDefacementProccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementProccess';
    private $hashingAlgorithm  = 'md5';
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
      $TransactionBatchjob_Update = TransactionBatchjob::where('mode','WebDefacement_scan')->first();
      $TransactionBatchjob_Update->progress = 2;
      $TransactionBatchjob_Update->transcation_date_start =date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->save();

      $WebdefacmentSetting_datas =  WebdefacmentSetting::where('active',1)->where('webdeflacement_progress',1)->whereNull('deleted_at')->get();
      foreach ($WebdefacmentSetting_datas as $key => $value) {
        $WebdefacmentSetting_update =   WebdefacmentSetting::find($value->id);
        $WebdefacmentSetting_update->webdeflacement_progress = 2;
        $WebdefacmentSetting_update->save();



        $webdefacment_id= $value->id;
        $WebdefacmentSetting_data =    $value;
        if ($WebdefacmentSetting_data) {
          $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id',$webdefacment_id)->first();
          if ($WebdefacmentDataOriginal_data) {

            $totalPoint = 0;
            $totalConfig = 0;
            $trackList['blacklist_parcent']  = 0;
            $trackList['file_size_parcent']  = 0;
            $trackList['hash_parcent']  = 0;
            $trackList['image_parcent']  = 0;
            $trackList['all_element_parcent'] = 0;
            $blackListFoundString = array();



            $url =$WebdefacmentSetting_data->url;
            $Keyword_check = array();
            $result = array();
            if ($this->is_url($url)) {
              $result["Result"] = 1;
              $result["messes "] = "";
              $result['hash_code'] = "";
              $result["file_size"] = 0 ;
              $result["all_element"] = 0 ;
              $result['image_url'] ="";
              $result["image_path_original"] ="";
              $result['blacklist'] =array();
              $message =' <div class="main-card-log">';
              $result['image1Hash']  = "";
              $result['image2Hash']  = "";
              $result['image_diff']  = null;


              $result['all_element_parcent']  = 0;
              $result['file_size_parcent']  =0;
              $result['hash_parcent']  = 0;
              $result['image_parcent']  = 0;
              $result['blacklist_parcent']  = 0;


              $image_path_2 ="";
              $response   = $this->getHtml($url);
              if ($response['content'] === FALSE){
                $webContent = "";
                $result["Result"] = 0;
                $result["messes "] = "Html not found";
              }else{
                $webContent = $response['content'];

                if ($WebdefacmentSetting_data->hash == 1) {




                 $totalConfig += 1;
                 $hashMD5 = hash($this->hashingAlgorithm, $webContent);
                 $result['hash_code'] = $hashMD5;
                 if ( $result['hash_code'] !=$WebdefacmentDataOriginal_data->hash)
                 {
                  $result['hash_parcent']  = 100;
                }else
                {
                  $result['hash_parcent']  = 0;
                }

                $message =$message.'
                <div class="card-log">
                <div class="card-log-body">
                <p>Hash Difference '.$result['hash_parcent'].'%</p>
                </div>
                </div>';

              }
              if ($WebdefacmentSetting_data->filesize == 1) {
               $totalConfig += 1;
                 $file_size = strlen($webContent);//filesize
                 $result["file_size"] = $file_size ;
                 $all_element_parcent = ($WebdefacmentDataOriginal_data->filesize- $result['file_size']);
                 if ($all_element_parcent == 0)
                 {
                  $result['file_size_parcent'] = 0;
                }

                else  if ($all_element_parcent == 1 || $all_element_parcent == -1)
                {
                  $result['file_size_parcent'] = 20;
                }
                else if ($all_element_parcent == 2 || $all_element_parcent == -2)
                {
                  $result['file_size_parcent'] = 40;
                }
                else if ($all_element_parcent == 3 || $all_element_parcent == -3)
                {
                  $result['file_size_parcent'] = 60;
                }
                else  if ($all_element_parcent == 4 || $all_element_parcent == -4)
                {
                  $result['file_size_parcent'] = 80;
                }else
                {
                  $result['file_size_parcent'] = 100; 
                }


                $message =$message.'
                <div class="card-log">
                <div class="card-log-body">
                <p>File Size Difference '.$result['file_size_parcent'].'%</p>
                </div>
                </div>';

              }
              if ($WebdefacmentSetting_data->element == 1) {
               $totalConfig += 1;
               $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $webContent, $matches);
               $result['all_element'] = (int)$allElement;
               $all_element_parcent = ($WebdefacmentDataOriginal_data->element - $result['all_element']);
               if ($all_element_parcent == 0)
               {
                $result['all_element_parcent'] = 0;
              }
              else if ($all_element_parcent <= 3)
              {
                $result['all_element_parcent'] = 20;
              }
              else if ($all_element_parcent <= 8)
              {
                $result['all_element_parcent'] = 40;
              }
              else if ($all_element_parcent < 12)
              {
                $result['all_element_parcent'] = 60;
              }
              else  if ($all_element_parcent <= 15)
              {
                $result['all_element_parcent'] = 80;
              }else
              {
                $result['all_element_parcent'] = 100; 
              }
              $message =$message.'
              <div class="card-log">
              <div class="card-log-body">
              <p>Element Difference '.$result['all_element_parcent'].'%</p>
              </div>
              </div>';

            }
            if ($WebdefacmentSetting_data->image_check == 1) {
              $totalConfig += 1;
              $url_id = $WebdefacmentDataOriginal_data->url_id;
              $site_id = $WebdefacmentSetting_data->site_id;
              $delay = $WebdefacmentSetting_data->delay_screen_shot_val;
              if (!$delay) {
               $delay =2000;
             }
             if (!$url_id) {
              $url_id =rand(10,100);
            }

            $image_name =  $site_id.'_'.$url_id.'_'.date("Y_m_d_His");
            $result["image_url"] = "/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name.".png";
            $path_include = base_path().'/public/screenshot/use/DownloadImage.php';
            include_once($path_include);
            $downloadImg = new \DownloadImage();
            $Path_image = base_path()."/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name.".png";
            $downloadImg->download($url,$Path_image,$delay);
            $result["image_path_original_full"] = $Path_image;
            $result["image_path_original"] = "/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name.".png";
            $result["url_id"] = $url_id;

            $WebdefacmentImageMark_check = WebdefacmentImageMark::where('webdefacment_data_original_id',$webdefacment_id)->get();
            if (count($WebdefacmentImageMark_check) > 0) {
              $dir_folder_image_original = base_path() . "/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
              $image_original = imagecreatefrompng($dir_folder_image_original);
              $black_original = ImageColorAllocate($image_original, 242, 242, 242);


              $dir_folder_image_compare = base_path() . "/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name.".png";
              $image_compare = imagecreatefrompng($dir_folder_image_compare);
              $black_compare = ImageColorAllocate($image_compare, 242, 242,242);

              foreach ($WebdefacmentImageMark_check as $WebdefacmentImageMark_checkkey => $WebdefacmentImageMark_checkvalue) {
                ImageFilledRectangle($image_original, $WebdefacmentImageMark_checkvalue->left,$WebdefacmentImageMark_checkvalue->top,$WebdefacmentImageMark_checkvalue->width,$WebdefacmentImageMark_checkvalue->hight,$black_original);

                ImageFilledRectangle($image_compare, $WebdefacmentImageMark_checkvalue->left,$WebdefacmentImageMark_checkvalue->top,$WebdefacmentImageMark_checkvalue->width,$WebdefacmentImageMark_checkvalue->hight,$black_compare);
              }

              ImagePng($image_original, base_path() ."/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original_custom.png");
              ImagePng($image_compare, base_path() ."/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name."_custom.png");
              $compareImage = $this->compareImage2(base_path()."/public/images/webdefacment_mages/".$site_id."/".$url_id."/",'image_original_custom.png',$image_name."_custom.png");



              $result["image_url"] = "/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name."_custom.png";
              $result["image_path_original"] = "/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name."_custom.png";
              $image_path_2  = "/public/images/webdefacment_mages/".$site_id."/".$url_id."/".$image_name.".png";

            }else{

              $compareImage = $this->compareImage2(base_path()."/public/images/webdefacment_mages/".$site_id."/".$url_id."/",'image_original.png',$image_name.".png");

            }


            if ($compareImage["diff"]  == 0)
            {
              $result['image_parcent']  = 0;
            }
            else if ($compareImage["diff"] < 5)
            { 
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 10;

            }else if($compareImage["diff"] < 11){
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 30;
            }
            else if($compareImage["diff"] < 20 ){
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 50;
            }
            else if($compareImage["diff"] < 30 ){
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 70;
            }
            else if($compareImage["diff"] < 40 ){
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 80;
            }else
            {
              $pageColorDiff = true;
              $totalPoint = $this->addPoint($totalPoint);
              $result['image_parcent']  = 100;
            }
            $result['image1Hash']  = $compareImage["image1Hash"];
            $result['image2Hash']  = $compareImage["image2Hash"];
            $result['image_diff']  =  $compareImage["diff"];

            $message =$message.'
            <div class="card-log">
            <div class="card-log-body">
            <p>image Difference '.$result['image_parcent'].'%</p>
            </div>
            </div>';
          }


          $result_checkDomainHeaders=   $this->checkDomainHeaders($url,1);
          $result_URL_404=   $this->URL_404($url);
          $result["DomainHeaders"] = $result_checkDomainHeaders;
          $result["Is_URL_404"] = $result_URL_404;

          if ($WebdefacmentSetting_data->blacklist_keyword == 1) {
            $blackListFound = $this->trackKeyWords($webContent,$WebdefacmentSetting_data->blacklist_keyword_content,$Keyword_check);
            $result['blacklist'] = $blackListFound;
            if (count($blackListFound) > 0)
            {
              $totalPoint = $this->addPoint($totalPoint);
              $result['blacklist_parcent']  = 100;
                // Format
              foreach($blackListFound as $value) {
                array_push($blackListFoundString,$value["key"]."(Position:[".$value["position"]."])");
              }


            }else
            {
              $result['blacklist_parcent']  = 0;
            }
            

            $message =$message.'
            <div class="card-log">
            <div class="card-log-body">
            <p>blacklist Difference '.$result['blacklist_parcent'].'%</p>
            </div>
            </div>';


          }



          $pointAlert = $this->calculatePoint2($result, $totalConfig);
          $status = 'Normal';
          if($pointAlert >=50 && $pointAlert < 74){
            $status = 'Meduim';
          }else if($pointAlert >= 75){
            $status = 'High';
          }
          $WebdefacmentDataCheck_save = new WebdefacmentDataCheck;
          $WebdefacmentDataCheck_save->webdefacment_setting_id = $webdefacment_id;
          $WebdefacmentDataCheck_save->hash_old =  $WebdefacmentDataOriginal_data->hash ;
          $WebdefacmentDataCheck_save->hash_new =   $result['hash_code'] ;
          $WebdefacmentDataCheck_save->hash_percent =   $result['hash_parcent'] ;
          $WebdefacmentDataCheck_save->filesize_old =    $WebdefacmentDataOriginal_data->filesize ;
          $WebdefacmentDataCheck_save->filesize_new =   $result['file_size'];
          $WebdefacmentDataCheck_save->filesize_percent =   $result['file_size_parcent'];
          $WebdefacmentDataCheck_save->element_old =    $WebdefacmentDataOriginal_data->element ;
          $WebdefacmentDataCheck_save->element_new =   $result['all_element'];
          $WebdefacmentDataCheck_save->element_percent =   $result['all_element_parcent'];


          $WebdefacmentDataCheck_save->image_old =    $WebdefacmentDataOriginal_data->imageHash ;
          $WebdefacmentDataCheck_save->image_new =   $result['image2Hash'];
          $WebdefacmentDataCheck_save->image_diff =   $result['image_diff'];
          $WebdefacmentDataCheck_save->image_percent =   $result['image_parcent'];

          $WebdefacmentDataCheck_save->image_url =   $result['image_url'];
          $WebdefacmentDataCheck_save->image_part =   $result['image_path_original'];
          $WebdefacmentDataCheck_save->keyword =   implode (",", $blackListFoundString);
          $WebdefacmentDataCheck_save->keyword_percent  = $result['blacklist_parcent'];
          $WebdefacmentDataCheck_save->last_update = date("Y-m-d H:i:s");
          $WebdefacmentDataCheck_save->percent_all = $pointAlert;
          $WebdefacmentDataCheck_save->status_code = $status;
          $WebdefacmentDataCheck_save->webdeflacement_progress = 1;

          if ($image_path_2) {
            $WebdefacmentDataCheck_save->image_part_2 = $image_path_2;
          }
          $WebdefacmentDataCheck_save->save();


          $WebdefacmentSetting_update =   WebdefacmentSetting::find($webdefacment_id);


          $color="#88ce4f  !important";
          if ($status=="High") {
           $color="#e64732 !important";
         }
         if ($status=="Meduim") {
           $color="#fcc838 !important";
         }

         $message =$message.'<div class="card-log"  style="background: '.$color.' "> <!-- ปล. ถ้าใส่สีให้ใช้แบบนี้นะครับ -->
         <div class="card-log-body">
         <p style="color: #fff">Total Difference '.$pointAlert.'% ('.$status.')</p>
         </div>
         </div>
         </div>';


         if ($status == 'Normal' || $status == 'Meduim') {
          $WebdefacmentSetting_update->webdeflacement_progress = 1;

          if ($status == 'Meduim') {
           $WebdefacmentDataLog_save = new WebdefacmentDataLog;
           $WebdefacmentDataLog_save->webdefacment_setting_id  = $webdefacment_id;
           $WebdefacmentDataLog_save->webdefacment_data_check_id  = $WebdefacmentDataCheck_save->id;
         //  $WebdefacmentDataLog_save->message ='hash:'.$result['hash_code'].'(percent:'.$result['hash_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>filesize:'.$result['file_size'].'(percent:'.$result['file_size_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>element:'.$result['all_element'].'(percent:'.$result['all_element_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>image:'.$result['image_diff'].'(percent:'.$result['image_parcent'].'%)';
      //   $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>blacklistKeywords:'.implode (",", $blackListFoundString);

           $WebdefacmentDataLog_save->hash_percent  = $result['hash_parcent'];
           $WebdefacmentDataLog_save->filesize_percent  = $result['file_size_parcent'];
           $WebdefacmentDataLog_save->element_percent  = $result['hash_parcent'];
           $WebdefacmentDataLog_save->image_percent  = $result['image_parcent'];
           $WebdefacmentDataLog_save->all_percent  = $pointAlert;
           $WebdefacmentDataLog_save->message =$message;
           $WebdefacmentDataLog_save->created_date  = date("Y-m-d H:i:s");
           $WebdefacmentDataLog_save->updated_date  = date("Y-m-d H:i:s");
           $WebdefacmentDataLog_save->status_val  =  $status;
           $WebdefacmentDataLog_save->save();
         }
       }else{
        $WebdefacmentSetting_update->webdeflacement_progress = 3;
        $WebdefacmentDataLog_save = new WebdefacmentDataLog;
        $WebdefacmentDataLog_save->webdefacment_setting_id  = $webdefacment_id;
        $WebdefacmentDataLog_save->webdefacment_data_check_id  = $WebdefacmentDataCheck_save->id;









       //  $WebdefacmentDataLog_save->message ='hash:'.$result['hash_code'].'(percent:'.$result['hash_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>filesize:'.$result['file_size'].'(percent:'.$result['file_size_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>element:'.$result['all_element'].'(percent:'.$result['all_element_parcent'].'%)';
        // $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>image:'.$result['image_diff'].'(percent:'.$result['image_parcent'].'%)';
      //   $WebdefacmentDataLog_save->message =$WebdefacmentDataLog_save->message.'<br>blacklistKeywords:'.implode (",", $blackListFoundString);
        $WebdefacmentDataLog_save->message =$message;
        $WebdefacmentDataLog_save->created_date  = date("Y-m-d H:i:s");
        $WebdefacmentDataLog_save->updated_date  = date("Y-m-d H:i:s");


        $WebdefacmentDataLog_save->hash_percent  = $result['hash_parcent'];
        $WebdefacmentDataLog_save->filesize_percent  = $result['file_size_parcent'];
        $WebdefacmentDataLog_save->element_percent  = $result['hash_parcent'];
        $WebdefacmentDataLog_save->image_percent  = $result['image_parcent'];
        $WebdefacmentDataLog_save->all_percent  = $pointAlert;


        $WebdefacmentDataLog_save->status_val  =  $status;
        $WebdefacmentDataLog_save->save();


      }

      $WebdefacmentSetting_update->image_last = $result['image_url'];
      $WebdefacmentSetting_update->last_check = date("Y-m-d H:i:s");
      $WebdefacmentSetting_update->last_online = date("Y-m-d H:i:s");
      $WebdefacmentSetting_update->status_val = $status;
      $WebdefacmentSetting_update->image_original = $result['image_url'];
      $WebdefacmentSetting_update->blacklist_keyword_current = $WebdefacmentDataCheck_save->keyword;
      $WebdefacmentSetting_update->save();

        //delete 4 last row
      $WebdefacmentDataLog_delete_list = array();
      $WebdefacmentDataLog_delete =  WebdefacmentDataLog::where('webdefacment_setting_id',$webdefacment_id)->orderBy('created_at','desc')->take(3)->get();
      foreach ($WebdefacmentDataLog_delete as $WebdefacmentDataLog_deletekey => $WebdefacmentDataLog_deletevalue) {
       array_push($WebdefacmentDataLog_delete_list, $WebdefacmentDataLog_deletevalue->id);
     }
     WebdefacmentDataLog::whereNotIn('id',$WebdefacmentDataLog_delete_list)->where('webdefacment_setting_id',$webdefacment_id)->delete();

   //delete 4 last row
     $WebdefacmentDataCheck_delete_list = array();
     $WebdefacmentDataCheck_delete =  WebdefacmentDataCheck::where('webdefacment_setting_id',$webdefacment_id)->orderBy('created_at','desc')->take(3)->get();
     foreach ($WebdefacmentDataCheck_delete as $WebdefacmentDataCheck_deletekey => $WebdefacmentDataCheck_deletevalue) {
       array_push($WebdefacmentDataCheck_delete_list, $WebdefacmentDataCheck_deletevalue->id);
     }
     WebdefacmentDataCheck::whereNotIn('id',$WebdefacmentDataCheck_delete_list)->where('webdefacment_setting_id',$webdefacment_id)->delete();




     print_r($webdefacment_id);


   }









 }else{
        // $result["Result"] = 0;
        // $result["messes "] = "The url is not formatted.";
 }
}else{
      // $result["Result"] = 0;
      // $result["messes "] = "No data found.";
}
}

$TransactionBatchjob_Update = TransactionBatchjob::where('mode','WebDefacement_scan')->first();
$TransactionBatchjob_Update->progress = 1;
$TransactionBatchjob_Update->transcation_date_end =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->save();

}







$result2 = array();
$result2["Result"] = 1;
$result2["messes "] = "";

//print_r($result2);
}


private function calculatePoint2($trackList, $totalConfig){

  $totalPoint = $trackList['all_element_parcent']+$trackList['file_size_parcent']+$trackList['hash_parcent']+ $trackList['image_parcent'] + $trackList['blacklist_parcent'];
  $result = $totalPoint/$totalConfig;
  return $result;
}

private function addPoint($totalPoint){
  return $totalPoint += 5;
}

private function compareImage2($dirPath,$imgSourcePath, $imgComparePath)
{
  $compareImage = array();
        // $imgSourcePath = PATH_CAPTURE_SCREEN.'/'.$imgSourcePath;
        // $imgComparePath = PATH_CAPTURE_SCREEN.'/'.$imgComparePath;

  $imgSourcePath = $dirPath.$imgSourcePath;
  $imgComparePath = $dirPath.$imgComparePath;

        // $imgSourcePath = "D:\ทดสอบรูปภาพ\\2017-06-14-02-43-01408692.jpg";
        //$imgComparePath =  "D:\ทดสอบรูปภาพ\\2017-06-15-20-37-12458813.jpg";

  $image1 = $imgSourcePath;
  $compareMachine = new compareImages($image1);
  $image1Hash = $compareMachine->getHasString(); 
  $compareImage["image1Hash"] = $image1Hash;


  $image2 =$imgComparePath;
  $image2Hash = $compareMachine->hasStringImage($image2); 
  $diff = $compareMachine->compareHash($image2Hash); 
  $compareImage["image2Hash"] = $image2Hash;
  $compareImage["diff"] = $diff;
  return $compareImage;
}
function is_url($uri){
  if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
    return $uri;
  }
  else{
    return false;
  }
}
private function getHtml($url) {

        // you can add some code to extract/parse response number from first header. 
        // For example from "HTTP/1.1 200 OK" string.

  $content =$this->get_dataa($url);

  return array(
    'content' => $content
  );
}
function get_dataa($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

private function trackKeyWords($webContent, $blacklistKeywords,$Keyword_checks)
{
  $keywordOK =array();
  if ($blacklistKeywords) {
   
    
    $keywords = explode(',', $blacklistKeywords);
    foreach ($keywords as $keyword) {
      $trackFound = $this->CheckKeyword($webContent,$keyword);
      if (count($trackFound) > 0)
      {
        if (count($Keyword_checks) > 0)
        {
          $filtereds = array();
          $rows = $Keyword_checks;
          foreach($rows as $index => $columns) {
            foreach($columns as $key => $value) {
              if ($key == 'key' && $value == $keyword) {
                $filtereds[] = $columns;
              }
            }
          }

          foreach ($trackFound as $position)
          {
                        //ถ้ามีให้หาตำแหน่ง

            if (!in_array($position, array_column($filtereds, 'position')))
            {
                            //ไม่มีอยู่ใน ignore
              array_push($keywordOK, array("key"=>$keyword,"position"=>$position));
            }
          }

        }else
        {
          foreach ($trackFound as $position)
          {
            array_push($keywordOK, array("key"=>$keyword,"position"=>$position));

          }
        }
      }
    }
  }

  return $keywordOK;
}

function CheckKeyword($html,$needle){
  $lastPos = 0;
  $positions = array();

  while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
    $positions[] = $lastPos;
    $lastPos = $lastPos + strlen($needle);
  }
  return $positions;
}
function checkDomainHeaders($url,$format=0)
{
  $url=parse_url($url);
  $end = "\r\n\r\n";
  $fp = fsockopen($url['host'], (empty($url['port'])?80:$url['port']), $errno, $errstr, 30);
  if ($fp)
  {
    $out  = "GET / HTTP/1.1\r\n";
    $out .= "Host: ".$url['host']."\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $var  = '';
    fwrite($fp, $out);
    while (!feof($fp))
    {
      $var.=fgets($fp, 1280);
      if(strpos($var,$end))
        break;
    }
    fclose($fp);

    $var=preg_replace("/\r\n\r\n.*\$/",'',$var);
    $var=explode("\r\n",$var);
    if($format)
    {
      foreach($var as $i)
      {
        if(preg_match('/^([a-zA-Z -]+): +(.*)$/',$i,$parts))
          $v[$parts[1]]=$parts[2];
      }
      return $v;
    }
    else
      return $var;
  }

}
function URL_404($url) {
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

  /* Get the HTML or whatever is linked in $url. */
  $response = curl_exec($handle);

  /* Check for 404 (file not found). */
  $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  curl_close($handle);

  /* If the document has loaded successfully without any redirection or error */
  if ($httpCode >= 200 && $httpCode < 300) {
    return 0;
  } else {
    return 1;
  }
}

private function isPageHack($trackList, $pageTransaction, &$totalPoint)
{
  $pageIsHack = false;

  foreach ($trackList as $key => $value) {
    $pageTransactionValue = $this->convertValueType($pageTransaction[$key]);            

    if ($pageTransactionValue !== $value) {
      $pageIsHack = true;
      $totalPoint = $this->addPoint($totalPoint);
    }
  }
  return $pageIsHack;
}

private function convertValueType($value)
{
  if (filter_var($value, FILTER_VALIDATE_INT)) {
    $value = (int)$value;
  }
  return $value;
}


}
