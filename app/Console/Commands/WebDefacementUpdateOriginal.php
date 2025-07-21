<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;

class WebDefacementUpdateOriginal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:WebDefacementUpdateOriginal {webdefacment_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementUpdateOriginal {webdefacment_id}';
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
       $result = array();
       $webdefacment_id= $this->argument('webdefacment_id');
       $WebdefacmentSetting_data =    WebdefacmentSetting::find($webdefacment_id);
       if ($WebdefacmentSetting_data) {
          $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id',$webdefacment_id)->first();
          if (!$WebdefacmentDataOriginal_data ) {
           $WebdefacmentDataOriginal_save = new WebdefacmentDataOriginal;
           $WebdefacmentDataOriginal_save->webdefacment_setting_id = $webdefacment_id;
           $WebdefacmentDataOriginal_save->save();
           $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id',$webdefacment_id)->first();
       }
       $url =$WebdefacmentSetting_data->url;
       $Keyword_check = array();




       if ($this->is_url($url)) {
          $result["Result"] = 1;
          $result["message"] = "";
          $result['hash_code'] = "";
          $result["file_size"] = 0 ;
          $result["all_element"] = 0 ;
          $result['image_url'] ="";
          $result["image_path_original"] ="";
          $response   = $this->getHtml($url);
          if ($response['content'] === FALSE){
            $webContent = "";
            $result["Result"] = 0;
            $result["message"] = "Html not found";
        }else{
            $webContent = $response['content'];

            if ($WebdefacmentSetting_data->hash == 1) {
              $hashMD5 = hash($this->hashingAlgorithm, $webContent);
              $result['hash_code'] = $hashMD5;
          }
          if ($WebdefacmentSetting_data->filesize == 1) {
                 $file_size = strlen($webContent);//filesize
                 $result["file_size"] = $file_size ;

             }
             if ($WebdefacmentSetting_data->element == 1) {
                $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $webContent, $matches);
                $result['all_element'] = (int)$allElement;

            }
            if ($WebdefacmentSetting_data->image_check == 1) {

             $url_id = $WebdefacmentDataOriginal_data->url_id;
             $site_id = $WebdefacmentSetting_data->site_id;
             $delay = $WebdefacmentSetting_data->delay_screen_shot_val;
             if (!$url_id) {
              $url_id =rand(10,100);
          }

          $result["image_url"] = "/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
          $path_include = base_path().'/public/screenshot/use/DownloadImage.php';
          include_once($path_include);
          $downloadImg = new \DownloadImage();
          $Path_image = base_path()."/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
          $downloadImg->download($url,$Path_image,$delay);
          $result["image_path_original_full"] = $Path_image;
          $result["image_path_original"] = "/public/images/webdefacment_mages/".$site_id."/".$url_id."/image_original.png";
          $result["url_id"] = $url_id;



          $compareMachine = new compareImages($result["image_path_original_full"]);
          $image1Hash = $compareMachine->getHasString(); 
          $result["imageHash"] = $image1Hash;


          $WebdefacmentDataOriginal_data->url_id = $result["url_id"];
          $WebdefacmentDataOriginal_data->imageHash = $result["imageHash"];

      }


      $result_checkDomainHeaders=   $this->checkDomainHeaders($url,1);
      $result_URL_404=   $this->URL_404($url);
      $result["DomainHeaders"] = $result_checkDomainHeaders;
      $result["Is_URL_404"] = $result_URL_404;
       // $blackListFound = $this->trackKeyWords($webContent,$WebdefacmentSetting_data->blacklist_keyword_content,$Keyword_check);//keyword

       //$result['blacklist'] = $blackListFound;



      $WebdefacmentDataOriginal_data->hash = $result['hash_code'];
      $WebdefacmentDataOriginal_data->filesize = $result['file_size'];
      $WebdefacmentDataOriginal_data->element = $result['all_element'];
      $WebdefacmentDataOriginal_data->image = $result['image_url'];
      $WebdefacmentDataOriginal_data->part_image = $result['image_path_original'];
      $WebdefacmentDataOriginal_data->last_update = date("Y-m-d H:i:s");
      $WebdefacmentDataOriginal_data->updated_at = date("Y-m-d H:i:s");
      $WebdefacmentDataOriginal_data->save();

      $parse = \parse_url($url);
           $host = $parse['host']; // prints 'google.com'

           $WebdefacmentSetting_data->DomainHeaders = json_encode($result_checkDomainHeaders);
           $WebdefacmentSetting_data->user_agent = $result_checkDomainHeaders['Server'];
           $WebdefacmentSetting_data->domain = $host;
           $WebdefacmentSetting_data->save();

       }





       $result["Result"] = 1;
       $result["message"] = "";



   }else{
     $result["Result"] = 0;
     $result["message"] = "The url is not formatted.";
 }
}else{
 $result["Result"] = 0;
 $result["message"] = "No data found.";
}
$result_json_e = json_encode($result);
echo $result_json_e;

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
}
