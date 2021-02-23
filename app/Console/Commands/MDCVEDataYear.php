<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\RSSFeedSettings\Entities\RSS;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use App\Entities\TransactionBatchjob;

class MDCVEDataYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDCVEDataYear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RSS Feed';

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

        $TransactionBatchjob_Update = TransactionBatchjob::where('mode','MDCVEDataYear')->first();
        $TransactionBatchjob_Update->progress = 1;
        $TransactionBatchjob_Update->transcation_date_start =date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->save();

        date_default_timezone_set("Asia/Bangkok");
        $serversql = env('DB_HOST');
        $dbuser =env('DB_USERNAME');
        $dbpass = env('DB_PASSWORD');
        $dbname =env('DB_DATABASE');
        $dbport =env('DB_PORT');
        $conn = mysqli_connect($serversql, $dbuser, $dbpass, $dbname, $dbport);

// download & setting //

        try {
            $year = date('Y');
            $output_filename = app_path()."/Console/Commands/temp/nvdcve-1.1-" . $year . ".json.zip";
            $linkurl = 'https://nvd.nist.gov/feeds/json/cve/1.1/nvdcve-1.1-' . $year . '.json.zip';
            $host = $linkurl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_REFERER, "http://nvd.nist.gov");
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);

    //print_r($result); // prints the contents of the collected file before writing..
            echo $output_filename;
            $fp = fopen($output_filename, 'w');
            fwrite($fp, $result);
            fclose($fp);

        } catch (Exception $e) {

        } finally {

        }

// extract file //
        ini_set('memory_limit', '1000M');

        $zip = \zip_open(app_path()."/Console/Commands/temp/nvdcve-1.1-" . $year . ".json.zip");

        if ($zip) {
            while ($zip_entry = \zip_read($zip)) {
                $_zip_entry_name = app_path()."/Console/Commands/temp/" . zip_entry_name($zip_entry);
        // echo "<p>";
        // echo "Found File : " . $_zip_entry_name . "<br />";
                if ($_zip_entry_name[strlen($_zip_entry_name) - 1] == '/') {
                    mkdir($_zip_entry_name);
                    chmod($_zip_entry_name, 0777);
                } else if (zip_entry_open($zip, $zip_entry)) {
                    $fname = app_path()."/Console/Commands/temp/" . zip_entry_name($zip_entry);
                    if ($fd = fopen($fname, 'w')) {
                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
                        fclose($fd);
                    } else {
                        echo "fopen($fname) error<br>";
                    }

                    \zip_entry_close($zip_entry);
//chmod($fname, 0775);
                }
        //echo "</p>";
            }

            zip_close($zip);

        }

////////////// real xaml file ///////////////

////////////// real xaml file ///////////////

        ini_set('memory_limit', '100000M');
        $strJsonFileContents = file_get_contents(app_path()."/Console/Commands/temp/nvdcve-1.1-" . $year . ".json") or die("Error: Cannot create object");
        $json_o = json_decode($strJsonFileContents, true);
        foreach ($json_o["CVE_Items"] as $json_data) {
            if ($json_data['cve']['data_type'] == "CVE" and explode('T', $json_data['publishedDate'])[0] >= date('Y-m-d', strtotime(' -90 day'))) {
   // if ($json_data['cve']['data_type'] == "CVE") {
                $CVE_Code = $json_data['cve']['CVE_data_meta']['ID'];
                print PHP_EOL . '=================================================================';
                print PHP_EOL . $CVE_Code . PHP_EOL;
                $array_cpe_match = array();
        //print_r($json_data['configurations']['nodes']);
                foreach ($json_data['configurations']['nodes'] as $vendorkey => $vendor) {
                    $array_cpe_match = array();
                    if (isset($vendor['children'])) {
                        foreach ($vendor['children'] as $children) {
                            foreach ($children['cpe_match'] as $cpe_match) {
                                if($cpe_match['vulnerable']==true)
                                    array_push($array_cpe_match, $cpe_match);

                            }
                        }
                    } 
                    if (isset($vendor['cpe_match'])) {
                        foreach ($vendor['cpe_match'] as $cpe_match) {
                            if($cpe_match['vulnerable']==true)
                                array_push($array_cpe_match, $cpe_match);
                        }
                    }
                }
               // print_r($array_cpe_match);
                print PHP_EOL . '==========================CPE=======================================';
                foreach ($array_cpe_match as $vendorkey => $vendor) {
                    $vendor_text = @$vendor['cpe23Uri'];
                    $vender_split = explode(":", $vendor_text);
            $vendor_name = @$vender_split[4]; //microsoft
            $product_name = @$vender_split[3];
            $product_version = @$vender_split[5];
            $product_edition = @$vender_split[6];
            if ($product_edition == "*") {
                $product_edition = "";
            }
            print PHP_EOL . 'vendor_name :' . $vendor_name;
            print PHP_EOL . 'product_name :' . $product_name;
            print PHP_EOL . 'product_version :' . $product_version;
            echo PHP_EOL . 'check...';

            $sql_samename = "SELECT namecve FROM fx_data_cveven WHERE namecve = '" . $CVE_Code . "' and title='" . $vendor_name . "' and vendor='" . $product_name . "' and version='" . $product_version . "'";
            $num =0;
            try {
              $result1 = mysqli_query($conn, $sql_samename) or die(mysqli_error());
              $num = mysqli_num_rows($result1);
          } catch (Exception $e) {

          }
          
          
            //echo 'end check';
            //$num  = 0;
          if ($num == 0) {
            $created_atz = date("Y-m-d H:i:s");
            $created_at = date("Y-m-d H:i:s", strtotime($created_atz));

            try {
                $sql = "INSERT INTO fx_data_cveven(namecve,title,vendor,version,edition,rawtext,created_at)
                VALUES ('" . $CVE_Code . "','" . $vendor_name . "','" . $product_name . "','" . $product_version . "','" . $product_edition . "','" .  $vendor_text . "','" .  $created_at . "' ) ";
                $result = mysqli_query($conn, $sql);
            } catch (Exception $e) {

            } finally {

            }

        }

            //--------------------------------

    }



    $description_data = "";
    foreach ($json_data['cve']['description']['description_data'] as $descriptionkey => $descriptionvalue) {
        $description_data = $description_data . $descriptionvalue['value'];
    }
    $description_data = htmlspecialchars($description_data, ENT_QUOTES);

   // print PHP_EOL . 'CVE  :' . $CVE_Code;
   // print PHP_EOL . 'impact-baseScore  :' . print_r($json_data['configurations']);
    $baseScore ="";
    $baseSeverity = "";
    try {
        $baseScore = $json_data['impact']['baseMetricV3']['cvssV3']['baseScore'];
        $baseSeverity = $json_data['impact']['baseMetricV3']['cvssV3']['baseSeverity'];

        if (!$json_data['impact']['baseMetricV3']['cvssV3']['baseScore']) {
            $baseScore = $json_data['impact']['baseMetricV2']['exploitabilityScore'];
            $baseSeverity = $json_data['impact']['baseMetricV2']['cvssV3']['severity'];

        }

    } catch (Exception $e) {

    }


    print PHP_EOL . 'baseScore :' . $baseScore;
    print PHP_EOL . 'baseSeverity :' . $baseSeverity;

    $publishedDate = $json_data['publishedDate'];
    $lastModifiedDate = $json_data['lastModifiedDate'];
    print PHP_EOL . 'publishedDate : ' . explode('T', $publishedDate)[0];
    print PHP_EOL . 'lastModifiedDate : ' . explode('T', $lastModifiedDate)[0];

//=================================
    $sql_samename = "SELECT namecve FROM fx_data_datacve WHERE namecve = '" . $CVE_Code . "'";
    $result1 = mysqli_query($conn, $sql_samename) or die(mysqli_error());
    $num = mysqli_num_rows($result1);
//$num = 0;


    if ($num > 0) {

        $add_name = $CVE_Code;

        $add_published = explode('T', $publishedDate)[0];

        $add_modified = explode('T', $lastModifiedDate)[0];

        $add_descript = $description_data;

        $add_cvsssore = $baseScore;

        $add_severity = $baseSeverity;

        $add_pub_datez = date("Y-m-d H:i:s ");
        $add_pub_date = date("Y-m-d H:i:s ", strtotime($add_pub_datez));

        $created_atz = date("Y-m-d H:i:s ");

        $created_at = date("Y-m-d H:i:s ", strtotime($created_atz));

        try {

           $this->update_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at);

       } catch (Exception $e) {

       } finally {

       }

   } else {

    $add_name = $CVE_Code;

    $add_published = explode('T', $publishedDate)[0];

    $add_modified = explode('T', $lastModifiedDate)[0];

    $add_descript = $description_data;

    $add_cvsssore = $baseScore;

    $add_severity = $baseSeverity;

    $add_pub_datez = date("Y-m-d H:i:s ");
    $add_pub_date = date("Y-m-d H:i:s ", strtotime($add_pub_datez));

    $created_atz = date("Y-m-d H:i:s ");

    $created_at = date("Y-m-d H:i:s ", strtotime($created_atz));

  //  try {

    $this->insert_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at);
    
  // } catch (Exception $e) {

  // } finally {

 //  }

}
        //==========================

}
    //break;


$TransactionBatchjob_Update = TransactionBatchjob::where('mode','MDCVEDataYear')->first();
$TransactionBatchjob_Update->progress = 1;
$TransactionBatchjob_Update->transcation_date_end =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->save();
}



}

function insert_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at)
{

    $insertdata = "INSERT INTO fx_data_datacve (namecve, published, modified, description, cvss_score, severity, updated_at, created_at)
    VALUES  ('" . $add_name . "',
    '" . $add_published . "',
    '" . $add_modified . "',
    '" . $add_descript . "',
    '" . $add_cvsssore . "',
    '" . $add_severity . "',
    '" . $add_pub_date . "','" . $created_at . "')";
    echo $insertdata;
    $insert_data_query = mysqli_query($conn, $insertdata);

    if ($insert_data_query) {
        echo "Insert Success";
        $st = array('sector' => "INSERT", 'status' => "Success");
        //  echo json_encode($st);
    } else {
        echo "Error" . mysqli_error($conn);
        $st = array('sector' => "INSERT", 'status' => "Fail");
        // echo json_encode($st);
    }

}

function update_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at)
{
    $updatedata = "UPDATE fx_data_datacve SET
    published = '" . $add_published . "' ,
    modified = '" . $add_modified . "' ,
    description = '" . $add_descript . "' ,
    cvss_score = '" . $add_cvsssore . "' ,
    severity = '" . $add_severity . "' ,
    updated_at = '" . $add_pub_date . "' ,
    created_at = '" . $created_at . "'
    WHERE namecve = '" . $add_name . "' ";

    $update_data_query = mysqli_query($conn, $updatedata);
    if ($update_data_query) {
        echo "Insert Success";
        $st = array('sector' => "UPDATE", 'status' => "Success");
        //  echo json_encode($st);
    } else {
        echo "Error" . mysqli_error($conn);
        $st = array('sector' => "UPDATE", 'status' => "Fail");
        //  echo json_encode($st);
    }

}

}
