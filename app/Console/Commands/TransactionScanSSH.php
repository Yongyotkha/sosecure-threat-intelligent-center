<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class TransactionScanSSH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:ssh';

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
        try {
            $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 0)->where('status', 1)->get();
            foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
                $TransactionTimeStampScan->created_at = Carbon::now();
                $TransactionTimeStampScan->progress = 1;
                $TransactionTimeStampScan->save();

                $domain = $TransactionTimeStampScan->get_domain->domain;
                $current_looking_for_subdomain = '';
                $current_looking_for_domain_name = '';
                $current_full_DNS_recon_all_detail = '';
                $current_DNS_recon_filter_for_ip_v4 = '';
                $current_DNS_recon_filter_for_ip_v6 = '';
                $current_looking_for_hijack_subdomain = '';
                $current_looking_for_compromised_email = '';
                $current_looking_for_all_compromised = '';
                $current_threat_intel_and_blacklist_lookups = '';
                $current_scraping_names_emails_and_phone_number = '';
                $current_port_scanner = '';

                $cmd = 'ssh -t root@10.104.0.12  /usr/spiderfoot/sf.py ';
                $cmd_looking_for_subdomain = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_crobat_api -s '.$domain.' -q -F AFFILIATE_DOMAIN_NAME,AFFILIATE_INTERNET_NAME,INTERNET_NAME';
                $cmd_looking_for_domain_name = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_crt,sfp_crobat_api -s '.$domain.' -q -F DOMAIN_NAME,DOMAIN_NAME_PARENT,DOMAIN_REGISTRAR,DOMAIN_WHOIS,INTERNET_NAME';
                $cmd_full_DNS_recon_all_detail = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_crobat_api,sfp_crt -s '.$domain.' -q';
                $cmd_DNS_recon_filter_for_ip_v4 = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_crobat_api,sfp_crt  -s '.$domain.' -q -r -F IP_ADDRESS';
                $cmd_DNS_recon_filter_for_ip_v6 = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_crobat_api,sfp_crt  -s '.$domain.' -q -r -F IPV6_ADDRESS';
                $cmd_looking_for_hijack_subdomain = $cmd.'-m sfp_dnsbrute,sfp_dnsresolve,sfp_dnsraw,sfp_spider,sfp_subdomain_takeover,sfp_crobat_api,sfp_crt,sfp_alienvault,sfp_alienvaultiprep,sfp_vxvault -s '.$domain.' -q -F AFFILIATE_INTERNET_NAME_HIJABLEACKABLE';
                $cmd_looking_for_compromised_email = $cmd.'-m sfp_spider,sfp_hunter,sfp_fullcontact,sfp_pgp,sfp_clearbit,sfp_emailformat,sfp_email,sfp_haveibeenpwned,sfp_citadel,sfp_intelx,sfp_scylla -s '.$domain.' -q -F EMAILADDR,EMAILADDR_COMPROMISED';
                $cmd_looking_for_all_compromised = $cmd.'-m sfp_spider,sfp_subdomain_takeover,sfp_hunter,sfp_fullcontact,sfp_pgp,sfp_clearbit,sfp_emailformat,sfp_email,sfp_haveibeenpwned,sfp_sorbs,sfp_spamcop,sfp_abusech,sfp_alienvault,sfp_malwarepatrol,sfp_citadel,sfp_intelx,sfp_scylla,sfp_cleantalk,sfp_torexits,sfp_voipbl,sfp_blocklistde,sfp_cinsscore,sfp_emergingthreats,sfp_greensnow -s '.$domain.' -q -F EMAILADDR_COMPROMISED,CCOUNT_EXTERNAL_OWNED_COMPROMISED,ACCOUNT_EXTERNAL_USER_SHARED_COMPROMISED,EMAILADDR_COMPROMISED,HASH_COMPROMISED,PASSWORD_COMPROMISED';
                $cmd_threat_intel_and_blacklist_lookups = $cmd.'-m sfp_sorbs,sfp_spamcop,sfp_abusech,sfp_alienvault,sfp_malwarepatrol,sfp_isc,sfp_virustotal,sfp_cloudflaredns,sfp_vxvault,sfp_botvrij,sfp_fortinet,sfp_cleantalk,sfp_torexits,sfp_voipbl,sfp_blocklistde,sfp_cinsscore,sfp_emergingthreats,sfp_greensnow -s '.$domain.' -q';
                $cmd_scraping_names_emails_and_phone_number = $cmd.'-m sfp_spider,sfp_names,sfp_email,sfp_phone,sfp_alienvault,sfp_alienvaultiprep,sfp_vxvault -s '.$domain.' -q -F HUMAN_NAME,EMAILADDR,PHONE_NUMBER';
                $cmd_port_scanner = $cmd.'-m sfp_portscan_tcp -s '.$domain.' -q -F TCP_PORT_OPEN,TCP_PORT_OPEN_BANNER';

                $descriptorspec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
                );
                flush();

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_looking_for_subdomain = proc_open($cmd_looking_for_subdomain, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_looking_for_subdomain)) {
                            while ($s = fgets($pipes[1])) {
                                $current_looking_for_subdomain .= $s;
                                echo $s;
                                flush();
                            }
                        }
                        
                        $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;
                        File::makeDirectory($path, $mode = 0777, true, true);
            
                        $file = $path.'/looking_for_subdomain.txt';
                        file_put_contents($file, $current_looking_for_subdomain);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_looking_for_domain_name = proc_open($cmd_looking_for_domain_name, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_looking_for_domain_name)) {
                            while ($s = fgets($pipes[1])) {
                                $current_looking_for_domain_name .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/looking_for_domain_name.txt';
                        file_put_contents($file, $current_looking_for_domain_name);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_full_DNS_recon_all_detail = proc_open($cmd_full_DNS_recon_all_detail, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_full_DNS_recon_all_detail)) {
                            while ($s = fgets($pipes[1])) {
                                $current_full_DNS_recon_all_detail .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/full_DNS_recon_all_detail.txt';
                        file_put_contents($file, $current_full_DNS_recon_all_detail);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_DNS_recon_filter_for_ip_v4 = proc_open($cmd_DNS_recon_filter_for_ip_v4, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_DNS_recon_filter_for_ip_v4)) {
                            while ($s = fgets($pipes[1])) {
                                $current_DNS_recon_filter_for_ip_v4 .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/DNS_recon_filter_for_ip_v4.txt';
                        file_put_contents($file, $current_DNS_recon_filter_for_ip_v4);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_DNS_recon_filter_for_ip_v6 = proc_open($cmd_DNS_recon_filter_for_ip_v6, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_DNS_recon_filter_for_ip_v6)) {
                            while ($s = fgets($pipes[1])) {
                                $current_DNS_recon_filter_for_ip_v6 .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/DNS_recon_filter_for_ip_v6.txt';
                        file_put_contents($file, $current_DNS_recon_filter_for_ip_v6);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_looking_for_hijack_subdomain = proc_open($cmd_looking_for_hijack_subdomain, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_looking_for_hijack_subdomain)) {
                            while ($s = fgets($pipes[1])) {
                                $current_looking_for_hijack_subdomain .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/looking_for_hijack_subdomain.txt';
                        file_put_contents($file, $current_looking_for_hijack_subdomain);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
                
                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_looking_for_compromised_email = proc_open($cmd_looking_for_compromised_email, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_looking_for_compromised_email)) {
                            while ($s = fgets($pipes[1])) {
                                $current_looking_for_compromised_email .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/looking_for_compromised_email.txt';
                        file_put_contents($file, $current_looking_for_compromised_email);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_looking_for_all_compromised = proc_open($cmd_looking_for_all_compromised, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_looking_for_all_compromised)) {
                            while ($s = fgets($pipes[1])) {
                                $current_looking_for_all_compromised .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/looking_for_all_compromised.txt';
                        file_put_contents($file, $current_looking_for_all_compromised);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_threat_intel_and_blacklist_lookups = proc_open($cmd_threat_intel_and_blacklist_lookups, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_threat_intel_and_blacklist_lookups)) {
                            while ($s = fgets($pipes[1])) {
                                $current_threat_intel_and_blacklist_lookups .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/threat_intel_and_blacklist_lookups.txt';
                        file_put_contents($file, $current_threat_intel_and_blacklist_lookups);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_scraping_names_emails_and_phone_number = proc_open($cmd_scraping_names_emails_and_phone_number, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_scraping_names_emails_and_phone_number)) {
                            while ($s = fgets($pipes[1])) {
                                $current_scraping_names_emails_and_phone_number .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/scraping_names_emails_and_phone_number.txt';
                        file_put_contents($file, $current_scraping_names_emails_and_phone_number);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                for ($i=1; $i <= 3; $i++) { 
                    try {
                        $process_port_scanner = proc_open($cmd_port_scanner, $descriptorspec, $pipes, realpath('./'), array());
                        if (is_resource($process_port_scanner)) {
                            while ($s = fgets($pipes[1])) {
                                $current_port_scanner .= $s;
                                echo $s;
                                flush();
                            }
                        }

                        $file = $path.'/port_scanner.txt';
                        file_put_contents($file, $current_port_scanner);
                        break;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }

                $TransactionTimeStampScan->progress = 2;
                $TransactionTimeStampScan->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
