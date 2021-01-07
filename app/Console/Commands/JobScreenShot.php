<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

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
        $path_include = base_path().'\\public\\screenshot\\use\\DownloadImage.php';
        include_once($path_include);

        $downloadImg = new \DownloadImage();
        
        echo "Start initial page transaction \r\n ";
        
        //$downloadImg->download('https://www.google.com/search?q=microweber/screen+with+youtube&sxsrf=ALeKk01ekFz6-zeiiCs3WnfgmEjW0hJQ2g:1610003755308&ei=K7X2X-OeEsff9QOfxL7QCw&start=10&sa=N&ved=2ahUKEwij4K-vo4nuAhXHb30KHR-iD7oQ8NMDegQIBBBN&biw=1455&bih=697', 'youtube');
        
        $dir_folder = app_path() . "\\Console\\Commands\\temp\\youtube.png";
        $image = imagecreatefrompng($dir_folder);
        $black = ImageColorAllocate($image, 0, 0, 0);
        ImageFilledRectangle($image, 300,0,1000,300,$black);

        ImagePng($image, app_path() ."\\Console\\Commands\\temp\\flag.png");
        ImageDestroy($image);
    }

    
}
