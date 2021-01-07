<?php

define('PATH_CAPTURE_SCREEN_MASTER', __DIR__ . '/../screen-master/');
define('PATH_PHANTOM_JS',  __DIR__ . '/../screen-master');
include_once  __DIR__ . '/../screen-master-v2/autoload.php';


class DownloadImage {
    function download($serverUrl, $imageName,$Delay){
        //$execCommand = PATH_PHANTOM_JS . '/bin/phantomjs '. PATH_PHANTOM_JS .'/bin/capturePage.js '. $serverUrl . ' '.PATH_CAPTURE_SCREEN_MASTER.$imageName;
       // exec($execCommand);
        
        $url =$serverUrl;
        $screen = new Screen\Capture();
        $screen->setDelay($Delay);
        $screen->setUrl($url);
        $screen->setOptions([
            'ignore-ssl-errors' => 'yes',
            'ssl-protocol' => "any",
            'web-security' => "true"
        ]);
        $screen->setWidth(intval('1024'));
        $screen->setHeight('768');
        $screen->setClipWidth(intval("0"));
        $screen->setClipHeight(intval("0"));
        $screen->setUserAgentString("e.g.: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36");
        $screen->setBackgroundColor("#ffffff");
        $screen->setImageType("png");
        $fileLocation = $imageName;
        try
        {
        	 $screen->save($fileLocation);
        }
        catch (Exception $exception)
        {
            echo $exception->getMessage()."\r\n";
        }
        
       
    }

    function generatePathImageMaster($url){
        date_default_timezone_set('Asia/Bangkok');
        $random_number = (new DateTime())->format('Y-m-d-H-i-su');

        $parse = parse_url($url);
        $url_Path_Image = PATH_CAPTURE_SCREEN_MASTER.'/master/'.substr($parse['host'],0,20);
        if (!file_exists($url_Path_Image)) {
            mkdir($url_Path_Image, 0777, true);
            // exec("mkdir -m a=rwx ".$url_Path_Image);
        }
        $imageName = $random_number;
        return 'master/'.substr($parse['host'],0,15).'/'.$imageName.'.png';
    }
    

}