<?php

namespace App\Console\Commands;
use App\Entities\Assets_datas;
use App\Entities\Data_datacve_mapping;
use App\Entities\Data_datacve_mapping_assest;

use Illuminate\Console\Command;

class CVEScanFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:CVEScanFile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CVEScanFile';

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

        
        $msgerror="";
        $xml = simplexml_load_file('/var/www/html/threat-intelligent-center/threat-intelligent-center/app/Console/Commands/FIle_CVE/ncb_10_130_zone_6wlp7n.xml'); //อ่านไฟล์ xml ที่อัพขึ้นมาล่าสุดบน host

        if($xml == null) //เช็คว่าอ่านไฟล์ xml ได้หรือเปล่า
        {           
            $msgerror = "Error Can't read file xml of";
          
        }
        else
        {

                    $count1 = count($xml->Report->ReportHost); //20
                     $count2 = count($xml->Report->ReportHost->ReportItem);//50
                  $ReportItemAllList =[];
                  $ReportItemAllList_data =[];
                  $ReportItemAllList_Duplication =array();
                  for ($i = 0 ; $i <= $count1 ;$i++)
                  {
                          if($xml->Report->ReportHost[$i] != null ) //ดักว่ามีReportItemไหม
                          {

                               $h =$xml->Report->ReportHost[$i]['name'];
                               $this->info($h);
                               $Assets_datas_check =  Assets_datas::where('value',$h)->first();
                               if($Assets_datas_check){
                                            for($j = 0 ; $j <= count($xml->Report->ReportHost[$i]->ReportItem) ;$j++)
                                            { 
                                                try {
                                                    if(isset($xml->Report->ReportHost[$i]->ReportItem[$j]['pluginFamily'])){

                                                                //  $xml_misc=  $xml->Report->ReportHost[$i]->ReportItem[$j]['pluginFamily']->__toString();
                                                                    if(1==1){
                                                                    
                                                                        if(isset($xml->Report->ReportHost[$i]->ReportItem[$j]->cve)){
                                                                            $cve_name = $xml->Report->ReportHost[$i]->ReportItem[$j]->cve;
                                                                            $Data_datacve_mapping_check = Data_datacve_mapping::where('namecve',$cve_name)->first();
                                                                      

                                                                            $score = $xml->Report->ReportHost[$i]->ReportItem[$j]->cvss3_base_score;
                                                                            if(!$score){
                                                                                $score =  $xml->Report->ReportHost[$i]->ReportItem[$j]->cvss_base_score;
                                                                            }
                                                                            $description = $xml->Report->ReportHost[$i]->ReportItem[$j]->description;
                                                                            $plugin_modification_date = $xml->Report->ReportHost[$i]->ReportItem[$j]->plugin_modification_date;
                                                                            $plugin_publication_date = $xml->Report->ReportHost[$i]->ReportItem[$j]->plugin_publication_date;
                                                                            $modification_date="";
                                                                            if($plugin_modification_date){
                                                                                $plugin_modification_date =str_replace("/","-",$plugin_modification_date);
                                                                            
                                                                            }
                                                                            if($plugin_publication_date){
                                                                                $plugin_publication_date =str_replace("/","-",$plugin_publication_date);
                                                                            
                                                                            }
                                                                            $score_name ="NONE";
                                                                            if($score <= 3.9){
                                                                                $score_name ="LOW";
                                                                            }else if($score >= 4 && $score <= 5.9){
                                                                                $score_name ="MEDIUM";

                                                                            }else if($score >= 7 && $score <= 8.9){

                                                                                $score_name ="HIGH";
                                                                            }else{
                                                                                $score_name ="CRITICAL";
                                                                            }
                                                                            $cve_id = 0;
                                                                            if(!$Data_datacve_mapping_check){
                                                                                $Data_datacve_mapping_save = new Data_datacve_mapping;
                                                                                $Data_datacve_mapping_save->namecve = $cve_name;
                                                                                $Data_datacve_mapping_save->published =$plugin_publication_date;
                                                                                $Data_datacve_mapping_save->modified =$plugin_modification_date;
                                                                                $Data_datacve_mapping_save->description =$description;
                                                                                $Data_datacve_mapping_save->cvss_score =$score;
                                                                                $Data_datacve_mapping_save->severity =$score_name;
                                                                                $Data_datacve_mapping_save->updated_at =date("Y-m-d");
                                                                                $Data_datacve_mapping_save->created_at =date("Y-m-d H:i:s");
                                                                                $Data_datacve_mapping_save->save(); 
                                                                               // $cve_id =$Data_datacve_mapping_save->id;

                                                                            }else{
                                                                               // $cve_id =  $Data_datacve_mapping_check->id;

                                                                            }
                                                                           $Data_datacve_mapping_assest_check =   Data_datacve_mapping_assest::where('namecve',$cve_name)->where('cve_asset_id',$cve_id)->where('site_id',$Assets_datas_check->site_id)->first();

                                                                            if(!$Data_datacve_mapping_assest_check){
                                                                                $Data_datacve_mapping_assest_save = new Data_datacve_mapping_assest;
                                                                                $Data_datacve_mapping_assest_save->namecve =  $cve_name;
                                                                                $Data_datacve_mapping_assest_save->cve_asset_id =   $Assets_datas_check->id;
                                                                                $Data_datacve_mapping_assest_save->site_id =  $Assets_datas_check->site_id;
                                                                                $Data_datacve_mapping_assest_save->code = $this->GUID();
                                                                                $Data_datacve_mapping_assest_save->updated_at =date("Y-m-d H:i:s");
                                                                                $Data_datacve_mapping_assest_save->created_at =date("Y-m-d H:i:s");
                                                                                $Data_datacve_mapping_assest_save->save();
                                                                            }
                                                                            $this->info(' >>>'.$cve_name);
                                                                            $this->info(' >>> >>>'.$score);
                                                                            $this->info(' >>> >>>'.$score_name);
                                                                            $this->info(' >>> >>> >>>'.$description);
                                                                            $this->info(' >>> >>> >>> plugin_modification_date '.$plugin_modification_date);
                                                                            $this->info(' >>> >>> >>> plugin_publication_date '.$plugin_publication_date);
                                                                            
                                                                        }
                                                                    
                                
                                                                }

                                                    }
                                                
                                                
                                                
                                                }
                                                catch(Exception $e) {
                                                
                                                }   
                                    
                                        
                                            }
                                }else{

                                    //ไม่มี assest ในฐานฐานข้อมูล
                                }
                         }
                       
                }


        }

     
        $this->info('✔︎ Demo data was reset successfully');
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