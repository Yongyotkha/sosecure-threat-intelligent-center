<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;

class JobScreenShot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:JobScreenShot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ScreenShot Feed';
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
        // $path_include = base_path().'\\public\\screenshot\\use\\SaveImage.php';
        // include_once($path_include);
        // $downloadImg = new \SaveImage();
        // echo "Start initial page transaction \r\n ";
        // $downloadImg->download('https://www.gsb.or.th/personal/personals/', 'gsb');
       $dir_folder = base_path() . "/public/images/webdefacment_mages/49/98/image_original.png";
       $image = imagecreatefrompng($dir_folder);
       $black = ImageColorAllocate($image, 0, 0, 0);
         //left,top,width,hight
       ImageFilledRectangle($image, 11.0312,152.5,751,308,$black);
       ImageFilledRectangle($image, 3.01953,717.617,1046,441,$black);
       ImagePng($image, base_path() ."/public/images/webdefacment_mages/49/98/image_original_custom.png");


       $dir_folder = base_path() . "/public/images/webdefacment_mages/49/98/2020-01-08-111213.png";
       $image = imagecreatefrompng($dir_folder);
       $black = ImageColorAllocate($image, 0, 0, 0);
         //left,top,width,hight
       ImageFilledRectangle($image, 11.0312,152.5,751,308,$black);
       ImageFilledRectangle($image, 3.01953,717.617,1046,441,$black);
       ImagePng($image, base_path() ."/public/images/webdefacment_mages/49/98/2020-01-08-111213_custom.png");

 $compareMachine = $this->compareImage2(base_path() . "/public/images/webdefacment_mages/49/98/",'image_original_custom.png','2020-01-08-111213_custom.png');//image
 var_dump($compareMachine) ;

        // echo json_encode($compareMachine);

        // 0 none
        // 1-49
        // 50-74
        // 75-84
        // 85-100

    //    $response   = $this->getHtml('https://packagist.org/packages/microweber/screen');
       // if ($response['content'] === FALSE){
        //    $webContent = "";
       // }else{
      //      $webContent = $response['content'];
     //   }
        //echo json_encode($webContent);

     //   $Keyword_check = array();
     //   $Keyword_check[]= array(
      //      'key'=>'If the format is',
       //     'value'=>24567
       // );

       // $file_size = strlen($webContent);//filesize
       // $blackListFound = $this->trackKeyWords($webContent,'If the format is,test',$Keyword_check);//keyword
       // $hashMD5 = hash($this->hashingAlgorithm, $webContent);//hash
        //$compareMachine = $this->compareImage2(app_path() . "\\Console\\Commands\\temp\\",'youtube.png','flag.png');//image
      //  $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $webContent, $matches);//element
      //  echo json_encode($blackListFound);
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

private function getHtml($url) {
    $content = file_get_contents($url);
        // you can add some code to extract/parse response number from first header. 
        // For example from "HTTP/1.1 200 OK" string.

    $c = curl_init('https://stackoverflow.com/questions/ask');
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($c);
    if (curl_error($c)){
        $status = null;
    }else{
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
    }
    curl_close($c);

    return array(
        'headers' => $status,
        'content' => $content
    );
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

                    if (!in_array($position, array_column($filtereds, 'value')))
                    {
                            //ไม่มีอยู่ใน ignore
                        array_push($keywordOK, array("key"=>$keyword,"value"=>$position));
                    }
                }

            }else
            {
                foreach ($trackFound as $position)
                {
                    array_push($keywordOK, array("key"=>$keyword,"value"=>$position));

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
}
