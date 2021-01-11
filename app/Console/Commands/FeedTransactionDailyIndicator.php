<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FeedTransactionDailyIndicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:FeedTransactionDailyIndicator';

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
        set_time_limit(0);
        ob_start();
        error_reporting(E_ALL);
        ini_set("display_errors",1);

        $mongo_url = 'mongodb://10.104.0.10:27017';
        $conn = new \MongoDB\Client($mongo_url);
    
        if(!$conn){
            die("Unable to connect with mongodb");
        }

        $db_name = 'sosecure_threatintelligent';
        $db = $conn->$db_name;
        $col1 = $db->fx_otx_events_indicator_ref;
        $filter = array('status' => 1); // where query
        $records = $col1->find($filter, ['sort' => ['feedtimepost' => -1], 'limit' => 10]);
        $docs = $records->toArray();

        $filename = public_path("indicator/indicator.csv");
        $handle = fopen($filename, 'w');
        fputcsv($handle, [
            "_id",
            "indicator_id",
            "pulse_id",
            "created",
            "created_at",
            "created_by",
            "deleted_at",
            "expiration",
            "is_active",
            "status",
            "transaction_date",
            "updated_at",
            "updated_by",
            "role",
        ]);
        foreach ($docs as $row) {
            fputcsv($handle, [
                $row->_id,
                $row->indicator_id,
                $row->pulse_id,
                $row->created,
                $row->created_at,
                $row->created_by,
                $row->deleted_at,
                $row->expiration,
                $row->is_active,
                $row->status,
                $row->transaction_date,
                $row->updated_at,
                $row->updated_by,
                $row->role,
            ]);
        }
        fclose($handle);

        if(file_exists(public_path("indicator/indicator.zip"))){
            unlink(public_path("indicator/indicator.zip"));
        }
        // Get real path for our folder
        $rootPath = realpath(public_path("indicator"));
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open(public_path('indicator/indicator.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        $zip->close();
        if(file_exists(public_path("indicator/indicator.csv"))){
            unlink(public_path("indicator/indicator.csv"));
        }
    }
}
