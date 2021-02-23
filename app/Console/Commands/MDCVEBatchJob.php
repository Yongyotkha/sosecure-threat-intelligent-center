<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\RSSFeedSettings\Entities\RSS;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use App\Entities\TransactionBatchjob;
use App\Entities\Transaction_client_cve_assets;

class MDCVEBatchJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDCVEBatchJob';

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

      $TransactionBatchjob_Update = TransactionBatchjob::where('mode','MDCVEBatchJob')->first();
      $TransactionBatchjob_Update->progress = 2;
      $TransactionBatchjob_Update->transcation_date_start =date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->save();



      date_default_timezone_set("Asia/Bangkok");
      $serversql = env('DB_HOST');
      $dbuser =env('DB_USERNAME');
      $dbpass = env('DB_PASSWORD');
      $dbname =env('DB_DATABASE');
      $dbport =env('DB_PORT');
      $conn = \mysqli_connect($serversql, $dbuser, $dbpass, $dbname, $dbport);

      if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    } else {

    //GET URL FORM DATABASE

        $urldata = "SELECT * FROM fx_logs_setting WHERE UPPER(type) = 'CVE' ";

        $result_final = mysqli_query($conn, $urldata);

    //echo "Download url form : $linkurl";
    }






    try {


        while ($row = $result_final->fetch_assoc()) {
    # code...



// $updatedata = "UPDATE data_batchjob SET datetime='" . date("Y-m-d H:i:s ") . "' where id =1";//
    // $update_data_query = mysqli_query($conn, $updatedata);//
    // if ($update_data_query) {
    //     echo "Insert Success";
    //     $st = array('sector' => "UPDATE", 'status' => "Success");
    //     //  echo json_encode($st);
    // } else {
    //     echo "Error" . mysqli_error($conn);
    //     $st = array('sector' => "UPDATE", 'status' => "Fail");
    //     //  echo json_encode($st);
    // }

########################################################################################

// download & setting //

            try {
                $linkurl = $row["link"];
                $path =  app_path()."/Console/Commands/temp/adddatabase/";
                $output_filename = app_path()."/Console/Commands/temp/adddatabase/nvdcve-1.1-modified.json.zip";
                $output_filename_json = app_path()."/Console/Commands/temp/adddatabase/nvdcve-1.1-modified.json";
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

                $fp = fopen($output_filename, 'w');
                fwrite($fp, $result);
                fclose($fp);
            } catch (Exception $e) {

            } finally {

            }

            ini_set('memory_limit', '1000M');
            echo $output_filename;
            $zip = \zip_open($output_filename);
            if ($zip) {
                while ($zip_entry = \zip_read($zip)) {
                    $_zip_entry_name = \zip_entry_name($zip_entry);
            //echo "<p>";
            //echo "Found File : " . $_zip_entry_name . "<br />";

                    if ($_zip_entry_name[strlen($_zip_entry_name) - 1] == '/') {
                        mkdir($_zip_entry_name);
                        chmod($_zip_entry_name, 0777);
                    } else if (zip_entry_open($zip, $zip_entry)) {
                        $fname = zip_entry_name($zip_entry);
                        if ($fd = fopen($path . $fname, 'w')) {
                            fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
                            fclose($fd);
                        } else {
                            echo "fopen($fname) error<br>";
                        }

                        zip_entry_close($zip_entry);
                //chmod($fname, 0775);
                    }
            //echo "</p>";
                }

                zip_close($zip);

            }

            ini_set('memory_limit', '100000M');
            $strJsonFileContents = file_get_contents($output_filename_json) or die("Error: Cannot create object");
            $json_o = json_decode($strJsonFileContents, true);

    ////////////// real xaml file ///////////////

    //search_revisions

            $site_id_fx_cve_assets = $row["site_id"];
            $sql = "SELECT * FROM fx_cve_assets WHERE active = 1 AND site_id = " . $site_id_fx_cve_assets . " ";
            $result = $conn->query($sql);
            $data_utable_list = [];

            if ($result->num_rows > 0) {
                while ($row2 = $result->fetch_assoc()) {

                    $data_utable = array();
                    $data_utable['vendor'] = $row2['vendor'];
                    $data_utable['title'] = $row2['title'];
                    array_push($data_utable_list, $data_utable);
                }
            } else {
                echo "0 results";
            }

            foreach ($json_o["CVE_Items"] as $json_data) {

               // if ($json_data['cve']['data_type'] == "CVE" and explode('T', $json_data['publishedDate'])[0] >= date('Y-m-d', strtotime(' -90 day'))) {
                if ($json_data['cve']['data_type'] == "CVE") {
                    $CVE_Code = $json_data['cve']['CVE_data_meta']['ID'];

                    foreach ($json_data['configurations']['nodes'] as $vendorkey => $vendor) {
                        try {
                            $array_cpe_match = array();
                            if (isset($vendor['children'])) {
                                foreach ($vendor['children'] as $children) {
                                    foreach ($children['cpe_match'] as $cpe_match) {
                                //if($cpe_match['vulnerable']==true)
                                        array_push($array_cpe_match, $cpe_match);

                                    }
                                }
                            } 

                            if (isset($vendor['cpe_match']))  {
                                foreach ($vendor['cpe_match'] as $cpe_match) {
                            //if($cpe_match['vulnerable']==true)
                                    array_push($array_cpe_match, $cpe_match);
                                }
                            }

                            if (!empty($array_cpe_match)) {
                                foreach ($array_cpe_match as $keycpe_match => $valuecpe_match) {
                                    if ($valuecpe_match['vulnerable'] == true) {
                                        $cpe23Uri = explode(":", $valuecpe_match['cpe23Uri']);
                                //print PHP_EOL.$cpe23Uri[3].PHP_EOL;

                                        $data_count = search_revisions($data_utable_list, $cpe23Uri[3], 'vendor', $cpe23Uri[4], 'title');


                                        if (count($data_count) > 0) {
                                            print PHP_EOL . '=================================================================';
                                            print PHP_EOL . $CVE_Code . PHP_EOL;

                                            $vendor_name = $cpe23Uri[3];
                                            $product_name = $cpe23Uri[4];
                                            $versionvalue = $cpe23Uri[5];
                                            $edition = $cpe23Uri[6];
                                            if ($edition == "*") {
                                                $edition = "";
                                            }
                                            echo PHP_EOL . 'check...';

                                            $sql_samename = "SELECT namecve FROM fx_data_cveven WHERE namecve = '" . $CVE_Code . "' and title='" . $product_name . "' and vendor='" . $vendor_name . "' and version='" . $versionvalue . "' and edition='" . $edition . "'";

                                            $result1 = mysqli_query($conn, $sql_samename) or die(mysqli_error());
                                            $num = mysqli_num_rows($result1);
                                    //echo 'end check';
                                    //$num  = 0;

                                            if ($num == 0) {
                                                $created_atz = date("Y-m-d H:i:s");
                                                $created_at = date("Y-m-d H:i:s", strtotime($created_atz));

                                                try {

                                                    $sql = "INSERT INTO fx_data_cveven(namecve,title,vendor,version,edition,created_at)
                                                    VALUES ('" . $CVE_Code . "','" . $product_name . "','" . $vendor_name . "','" . $versionvalue . "','" . $edition . "','" . $created_at . "')";
                                                    $result = mysqli_query($conn, $sql);

                                                } catch (Exception $e) {

                                                } finally {

                                                }

                                            }

                                    //================

                                            $description_data = "";
                                            if(isset($json_data['cve']['description']['description_data'])){
                                                foreach ($json_data['cve']['description']['description_data'] as $descriptionkey => $descriptionvalue) {
                                                    $description_data = $description_data . $descriptionvalue['value'];
                                                }
                                            }
                                    // else{
                                    //     foreach ($json_data['cve']['description'] as $descriptionkey => $descriptionvalue) {
                                    //         $description_data = $description_data . $descriptionvalue['value'];
                                    //     }
                                    // }

                                            $description_data = htmlspecialchars($description_data, ENT_QUOTES);

                                            $baseScore = $json_data['impact']['baseMetricV3']['cvssV3']['baseScore'];
                                            $baseSeverity = $json_data['impact']['baseMetricV3']['cvssV3']['baseSeverity'];

                                            if (!$json_data['impact']['baseMetricV3']['cvssV3']['baseScore']) {
                                                $baseScore = $json_data['impact']['baseMetricV2']['exploitabilityScore'];
                                                $baseSeverity = $json_data['impact']['baseMetricV2']['cvssV3']['severity'];

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

                                                    update_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at);

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

                                                try {

                                                    insert_nvd($conn, $add_name, $add_published, $add_modified, $add_descript, $add_cvsssore, $add_severity, $add_pub_date, $created_at);

                                                } catch (Exception $e) {

                                                } finally {

                                                }

                                            }
                                    //==========================

                                    //==========

                                        }

                                    }
                                }
                            }

                        } catch (Exception $e) {

                        }

                    }

                }
        //break;
            }
// extract file //


            $sql = "SELECT * FROM fx_cve_assets";
            $result2 = mysqli_query($conn, $sql) or die(mysqli_error());

    //Save Mapping
            echo "=====================Maping===================//////";
    /*
    $urldata2 = "SELECT * FROM fx_logs_setting WHERE id LIKE 1 ";

    $resulturldata2 = mysqli_query($conn, $urldata2);
    $rowresulturldata2 = $resulturldata2->fetch_assoc();

    $urldata3 = "SELECT mode FROM fx_logs_setting WHERE id LIKE 3 ";

    $resulturldata3 = mysqli_query($conn, $urldata3);
    $rowresulturldata3 = $resulturldata3->fetch_assoc();
    */

    while ($row3 = $result2->fetch_array(MYSQLI_ASSOC)) {

       //   echo '<br>'.$row3['vendor'].'<br>---------------------------------------/r/n';

        $sql2 = "SELECT distinct * FROM fx_data_cveven where vendor = '" . $row3['vendor'] . "' and title='" . $row3['title'] . "' and version='" . $row3['version'] . "'";


        $result3 = mysqli_query($conn, $sql2) or die(mysqli_error());
        $namecveList = array();
        array_push($namecveList, 'C0000');
        array_push($namecveList, 'C0001');
         // print PHP_EOL . $sql2;
        while ($row4 = $result3->fetch_array(MYSQLI_ASSOC)) {
            if (!array_key_exists($row4['namecve'], $namecveList)) {
                array_push($namecveList, $row4['namecve']);
            }
            
        }

       // print_r($namecveList);

        $created_atz = date("Y-m-d H:i:s ");
        $modified = date("Y-m-d", strtotime($created_atz));

        // $modified_start = date('Y-m-d', strtotime(' -1 day'));
        // $sql3 =   "SELECT * FROM fx_data_datacve WHERE modified between '".$modified_start."' AND '".$modified."' and namecve IN ('".implode("','", $namecveList)."') ";
        
        //$modified ='2019-08-08';
        $sql3  = 'SELECT *
        FROM `fx_data_datacve`
        WHERE  modified >=CURDATE() + INTERVAL -90 DAY and   namecve IN (' . "'".implode("','", $namecveList)."'" . ')';

        // $sql3  = 'SELECT *
        // FROM `data_datacve`
        // WHERE  namecve IN (' . "'".implode("','", $namecveList)."'" . ')';

        // $sql3 = 'SELECT *
        // FROM `data_datacve`
        // WHERE modified > "2021-01-01" and  namecve IN (' . "'".implode("','", $namecveList)."'" . ')';
        // echo  $sql3;

        $result4 = mysqli_query($conn, $sql3) or die(mysqli_error());
        
        while ($row4 = $result4->fetch_array(MYSQLI_ASSOC) ) {

            $created_atz = date("Y-m-d H:i:s ");
            $created_at = date("Y-m-d H:i:s ", strtotime($created_atz));


            $sql_samename = "SELECT namecve FROM fx_data_datacve_mapping WHERE namecve = '" . $row4['namecve'] . "'";

            $result1 = mysqli_query($conn, $sql_samename) or die(mysqli_error());
            $num = mysqli_num_rows($result1);
            
            if ($num == 0) {
                $insertdata = "INSERT INTO fx_data_datacve_mapping (namecve, published, modified, description, cvss_score, severity,site_id, updated_at, created_at,cveven_id)
                VALUES  ('" . $row4['namecve'] . "',
                '" . $row4['published'] . "',
                '" . $row4['modified'] . "',
                '" . $row4['description'] . "',
                '" . $row4['cvss_score'] . "',
                '" . $row4['severity'] . "',
                '" . '0' . "',
                '" . $row4['updated_at'] . "','" . $created_at . "','" . '0' . "')";




                $result = \mysqli_query($conn, $insertdata);



                // $sql_samename_last = "SELECT id FROM fx_data_datacve_mapping order by id desc";
                // $result1_last = mysqli_query($conn, $sql_samename_last) or die(mysqli_error());
                // $num_last = mysqli_fetch_assoc($result1_last);
                
                // $insertdata_mapping = "INSERT INTO fx_transaction_client_data_datacve_mapping (site_id, transaction_id, transaction_mode, transaction_data_status, status, created_at)
                // VALUES  ('" . $row3['site_id'] . "',
                // '" .  $num_last['id'] . "',
                // '" . "insert". "',
                // '" . "1". "','" . "1". "','" .  date("Y-m-d H:i:s ") . "')";
                // $result = \mysqli_query($conn, $insertdata_mapping);

            }else{
                $sql_samename_last ="UPDATE `fx_data_datacve_mapping`
                SET
                `published` = '". $row4['published']."',
                `modified` = '". $row4['modified']."',
                `description` = '". $row4['description']."',
                `cvss_score` = '". $row4['cvss_score']."',
                `severity` = '". $row4['severity']."',
                `updated_at` = '". $row4['updated_at']."',
                WHERE `id` > 0 and  namecve = '" . $row4['namecve'] . "'";
                $result = \mysqli_query($conn, $sql_samename_last);

            }

            // Mapping Asset

            $sql_samename_asset = "SELECT namecve FROM fx_data_datacve_mapping_assets WHERE namecve = '" . $row4['namecve'] . "' and site_id='".$row3['site_id']."' and cve_asset_id='".$row3['id']."'";

            $result11 = mysqli_query($conn, $sql_samename_asset) or die(mysqli_error());
            $num = mysqli_num_rows($result11);
            if ($num == 0) {
                $insertdata_asset = "INSERT INTO fx_data_datacve_mapping_assets (namecve,cve_asset_id,site_id,code,updated_at, created_at)
                VALUES  ('" . $row4['namecve'] . "',
                '" . $row3['id'] . "',
                '" . $row3['site_id']. "',
                '" . $This->GUID(). "',
                '" . $row4['updated_at'] . "','" . $created_at . "'". ")";

                echo  $insertdata_asset;


                $result = \mysqli_query($conn, $insertdata_asset);
            }else{

            }



            if (1 == 2) {
                //send log
                // usleep(1.5*1000000);
                $mode = $row["protocal_format"];
                if ($mode == 1) {

                    $LogString = "CEF:0|SOSecure|CVE|1.0|101|Vulnerability Detection|1| dst=" . $row3['IP'] . " dhost=" . $row3['Hostname'] . " dvchost=" . $row3['Site'] . ' cs1Label=NameCVE cs1=' . $row4['namecve'] . ' cs2Label=Vendor ' . "cs2=" . $row3['vendor'] . " " . 'cs3Label=OS ' . "cs3=" . $row3['title'] . " " . 'cs4Label=OSVersion ' . "cs4=" . $row3['version'] . " " . 'cs5Label=Edition ' . "cs5=" . $row3['edition'] . " " . 'cs6Label=Severity ' . "cs6=" . $row4['cvss_score'] . "-" . $row4['severity'];

                } else {

                    $LogString = date("Y/M/d H:i:s") . " " . $row3['vendor'] . " CVE_ID=" . $row4['namecve'] . ",DESCRIPTION=" . $row4['description'] . ",CVSS=" . $row4['cvss_score'] . ",DEVICE=" . $row3['IP'] . ",VENDOR=" . $row3['vendor'] . ",SITE=" . $row3['Site'] . "";

                }

                if ($row['protocol'] == "udp") {
                    //  system("echo '".$LogString."' | nc -w0 -u ".$rowresulturldata2['ip']." ".$rowresulturldata2['port']);
                    //  echo  "echo '".$LogString."' | nc -w0 -u ".$rowresulturldata2['ip']." ".$rowresulturldata2['port']."<br>";

                    $server_ip = $row['ip'];
                    $server_port = $row['port'];
                    $beat_period = 1;
                    $message = $LogString;
                    if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
                        socket_sendto($socket, $message, strlen($message), 0, $server_ip, $server_port);

    //echo '\n'.$LogString.'\n';

                    } else {
                    }

                } else {

                    $server_ip = $row['ip'];
                    $server_port = $row['port'];
                    $beat_period = 1;
                    $message = $LogString;

                    ini_set('display_errors', true);
                    error_reporting(E_ALL); // <- for debugging purposes only

                    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                    if (!$socket) {
                        $errno = socket_last_error();
                        $error = sprintf('%s (%d)', socket_strerror($errno), $errno);
                        trigger_error($error, E_USER_ERROR);
                    }

                    if (!socket_connect($socket, $server_ip, $server_port)) {
                        $errno = socket_last_error($socket);
                        $error = sprintf('%s (%d)', socket_strerror($errno), $errno);
                        trigger_error($error, E_USER_ERROR);
                    }

                    $buff = $message;
                    $length = strlen($buff);
                    $sent = socket_write($socket, $buff, $length);
                    if (false === $sent) {
                        $errno = socket_last_error($socket);
                        $error = sprintf('%s (%d)', socket_strerror($errno), $errno);
                        trigger_error($error, E_USER_ERROR);
                    } else if ($length !== $sent) {
                        $msg = sprintf('only %d of %d bytes sent', $length, $sent);
                        trigger_error($msg, E_USER_NOTICE);
                    }

                }

            }

        }
    }

}

} catch (Exception $th) {
    echo json_encode($th->getMessage());
}


$TransactionBatchjob_Update = TransactionBatchjob::where('mode','MDCVEBatchJob')->first();
$TransactionBatchjob_Update->progress = 1;
$TransactionBatchjob_Update->transcation_date_end =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
$TransactionBatchjob_Update->save();

}

function search_revisions($dataArray, $search_value, $key_to_search, $other_matching_value = null, $other_matching_key = null)
{
    // This function will search the revisions for a certain value
    // related to the associative key you are looking for.
    $keys = array();
    foreach ($dataArray as $key => $cur_value) {
        if ($cur_value[$key_to_search] == $search_value) {
            if (isset($other_matching_key) && isset($other_matching_value)) {
                if ($cur_value[$other_matching_key] == $other_matching_value) {
                    $keys[] = $cur_value;
                }
            } else {
                // I must keep in mind that some searches may have multiple
                // matches and others would not, so leave it open with no continues.
                $keys[] = $cur_value;
            }
        }
    }
    return $keys;
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

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
}
