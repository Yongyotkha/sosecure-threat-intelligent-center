<?php

namespace Modules\Scans\Http\Controllers;

use App\DataScans;
use App\DataTypes;
use App\transaction_client_asset;
use App\transaction_client_asset_data;
use App\TransactionScans;
use App\TransactionTimeStampScans;
use Auth;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Assets\Entities\Assets_port;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Domain;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class ScansController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $data['page'] = langapp('scans');
        return view('scans::index')->with($data);
    }

    public function scan_domain($tab = 'overview', $site_code)
    {
        $allowed = ['overview', 'datatype', 'asset', 'settings', 'logs'];
        $tab = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab'] = $tab;
        $SiteSettings = TransactionTimeStampScans::where('code', $site_code)->first();
        $data['site'] = $SiteSettings;
        if ($tab == 'overview') {
            $DataScans = DataScans::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->orderBy('total', 'desc')->take(5)->get();
            $data['DataScans'] = $DataScans;
        }
        $DomainFor = Domain::select('code')->withTrashed()->where('id',$SiteSettings->domain_id)->first();
        $SiteSettingsfor = SiteSettings::select('code')->withTrashed()->where('id', $SiteSettings->site_id)->first();
        $data['menu'] = 'scan';
        $data['sitecode'] = $SiteSettingsfor->code;
        $data['domaincode'] = $DomainFor->code;
        return view('scans::scans_domain')->with($data);
    }

    public function get_referent(Request $request)
    {
        foreach ($request->values as $key => $data) {
            $TransactionScans[$key]['raw_data'] = $data['raw_data'];
            $TransactionScans[$key]['selected_type'] = $data['data_type'] ?? '';
            
            // Identify "Roots" for this item to find related records
            $roots = [];
            $dataType = $data['data_type'] ?? '';
            $rawData = $data['raw_data'] ?? '';
            $referent = $data['referent'] ?? '';
            $ipAddress = $data['ip_address'] ?? '';

            if (in_array($dataType, ['IP Address', 'IPv6 Address', 'Domain Name', 'Subdomain', 'Internet Name', 'Network'])) {
                $roots[] = $rawData;
            }
            if (!empty($referent)) $roots[] = $referent;
            if (!empty($ipAddress)) $roots[] = $ipAddress;
            
            $roots = array_unique(array_filter($roots));

            $query = TransactionScans::where('site_id', $data['site_id'])
                ->where('domain_id', $data['domain_id']);

            if (count($roots) > 0) {
                $query->where(function ($q) use ($roots) {
                    $q->whereIn('raw_data', $roots)
                      ->orWhereIn('referent', $roots)
                      ->orWhereIn('ip_address', $roots);
                });
            } else if (!empty($data['id'])) {
                // Fallback to strict ID if no roots found
                $query->where('id', $data['id']);
            }

            $TransactionScans[$key]['data'] = $query->get();
        }
        $DataTypes = DataTypes::where('status', 1)->get();
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $TransactionScans, 'data_type' => $DataTypes]);
    }

    public function get_data_type()
    {
        $DataTypes = DataTypes::all();
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data_type' => $DataTypes]);
    }

    public function save_assets(Request $request)
    {

        foreach ($request->assets as $data) {
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
            if (!$Assets) {
                $Assets = new Assets;
                $Assets->code = generator_uuid();
                $Assets->created_by = Auth::user()->id;
                $Assets->site_id = $data['site_id'];
                $Assets->domain_id = $data['domain_id'];
                $Assets->status = 1;
                $Assets->raw_data = $data['raw_data'];
                $Assets->save();

                $transaction_client_asset = transaction_client_asset::where('site_id', $data['site_id'])->where('transaction_id', $Assets->id)->first();
                if($transaction_client_asset){
                    $transaction_client_asset -> transaction_mode = 'insert';
                    $transaction_client_asset -> transaction_data_status = 1;
                    $transaction_client_asset -> status = 1;
                    $transaction_client_asset -> save();
                }else{
                    $transaction_client_asset = new transaction_client_asset();
                    $transaction_client_asset -> site_id = $data['site_id'];
                    $transaction_client_asset -> transaction_id = $Assets->id;
                    $transaction_client_asset -> transaction_mode = 'insert';
                    $transaction_client_asset -> transaction_data_status = 1;
                    $transaction_client_asset -> status = 1;
                    $transaction_client_asset -> save();
                }
            }
            $savedAssetsDataMap = [];
            $cveAssetIdMap = []; // Track actual cve_assets.id for mapping

            // Identify the actual IP Address for this asset to ensure "By IPs" view is correct
            $ipTypeId = \App\DataTypes::where('value', 'IP Address')->value('id');
            $assetIP = \Modules\Scans\Entities\AssetsData::where('asset_id', $Assets->id)
                ->where('data_type_id', $ipTypeId)
                ->value('value') ?: $Assets->raw_data;

            foreach ($request->assets_data as $item) {
                if (!$item || !isset($item['raw_data_base'])) continue;

                // If we are saving an IP Address in this request, update our assetIP
                if ($item['data_type'] == $ipTypeId) {
                    $assetIP = $item['raw_data'];
                }
                \Log::debug("Saving AssetsData... item[data_type]: " . ($item['data_type'] ?? 'N/A') . ", item[raw_data]: " . ($item['raw_data'] ?? 'N/A') . ", item[raw_data_base]: " . ($item['raw_data_base'] ?? 'N/A') . (isset($item['ip_address']) ? ", item[ip_address]: " . $item['ip_address'] : ""));
                
                // If ip_address is provided, use it instead of the domain as the base for ports and other findings
                $mappingBase = (!empty($item['ip_address'])) ? $item['ip_address'] : $item['raw_data_base'];

                // --- SMART ASSET LINKING ---
                // Find or create the correct parent Asset based on mappingBase
                $currentAsset = Assets::where('raw_data', $mappingBase)->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
                if (!$currentAsset) {
                    $currentAsset = new Assets;
                    $currentAsset->code = generator_uuid();
                    $currentAsset->created_by = Auth::user()->id;
                    $currentAsset->site_id = $data['site_id'];
                    $currentAsset->domain_id = $data['domain_id'];
                    $currentAsset->status = 1;
                    $currentAsset->raw_data = $mappingBase;
                    $currentAsset->save();

                    $tr_asset = transaction_client_asset::where('site_id', $data['site_id'])->where('transaction_id', $currentAsset->id)->first();
                    if (!$tr_asset) {
                        $tr_asset = new transaction_client_asset();
                        $tr_asset->site_id = $data['site_id'];
                        $tr_asset->transaction_id = $currentAsset->id;
                    }
                    $tr_asset->transaction_mode = 'insert';
                    $tr_asset->transaction_data_status = 1;
                    $tr_asset->status = 1;
                    $tr_asset->save();
                }
                // --- END SMART ASSET LINKING ---

                // Ensure this Asset has an identity (Subdomain or IP) saved in assets_datas to display correctly in Host list
                $identityType = (filter_var($mappingBase, FILTER_VALIDATE_IP)) ? 5 : 14; 
                $assetIdentity = AssetsData::where('asset_id', $currentAsset->id)
                    ->where('value', $mappingBase)
                    ->where('data_type_id', $identityType)
                    ->where('site_id', $data['site_id'])
                    ->first();
                if (!$assetIdentity) {
                    $assetIdentity = new AssetsData;
                    $assetIdentity->code = generator_uuid();
                    $assetIdentity->created_by = Auth::user()->id;
                    $assetIdentity->site_id = $data['site_id'];
                    $assetIdentity->domain_id = $data['domain_id'];
                    $assetIdentity->status = 1;
                    $assetIdentity->value = $mappingBase;
                    $assetIdentity->data_type_id = $identityType;
                    $assetIdentity->asset_id = $currentAsset->id;
                    $assetIdentity->save();
                }


                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                    ->where('domain_id', $data['domain_id'])
                    ->where('value', $item['raw_data'])
                    ->where('data_type_id', $item['data_type'])
                    ->where('asset_id', $currentAsset->id)
                    ->first();
                
                if (!$AssetsData) {
                    // Always save if we have a valid parent (which we now do via Smart Asset Linking)
                    $AssetsData = new AssetsData;
                    $AssetsData->code = generator_uuid();
                    $AssetsData->created_by = Auth::user()->id;
                    $AssetsData->site_id = $data['site_id'];
                    $AssetsData->domain_id = $data['domain_id'];
                    $AssetsData->status = 1;
                    $AssetsData->value = $item['raw_data'];
                    $AssetsData->data_type_id = $item['data_type'];
                    $AssetsData->asset_id = $currentAsset->id;
                    
                    // ARCHITECTURAL IMPROVEMENT: Set parent relationship
                    // If it's not the identity itself (IP/Domain), link it to the identity
                    if ($item['raw_data'] != $mappingBase) {
                        $AssetsData->refer_asset_id = $assetIdentity->id;
                    }
                    
                    $AssetsData->save();
                }

                if ($AssetsData && $item['data_type'] != 17) {
                    $savedAssetsDataMap[$item['raw_data']] = $AssetsData->id;
                }

                if ($AssetsData && $item['data_type'] == 17) {
                    // Add to CPE and cve_assets tables if it's a CPE
                    $cpe_string = $item['raw_data'];
                    $cpe_parts = explode(':', $cpe_string);
                    $vendor_name = isset($cpe_parts[3]) ? $cpe_parts[3] : '';
                    $product_name = isset($cpe_parts[4]) ? $cpe_parts[4] : '';
                    $product_version = isset($cpe_parts[5]) ? $cpe_parts[5] : '*';
                    $product_edition = isset($cpe_parts[6]) ? $cpe_parts[6] : '*';

                    // Find the parent AssetsData (IP, Domain or Port) to link the CPE to
                    // BEST PRACTICE: Link to the assetIdentity (IP or Domain) for maximum visibility in the UI
                    $cpe_asset_id = $assetIdentity->id; 

                    $newCPE = \Modules\Scans\Entities\CPE::where('asset_id', $cpe_asset_id)->where('result', $cpe_string)->first();
                    if (!$newCPE) {
                        $newCPE = new \Modules\Scans\Entities\CPE();
                        $newCPE->code = generator_uuid();
                    }
                    $newCPE->asset_id = $cpe_asset_id; 
                    $newCPE->result = $cpe_string;
                    $newCPE->vendor = $vendor_name;
                    $newCPE->title = $product_name;
                    $newCPE->version = $product_version;
                    $newCPE->edition = $product_edition;
                    
                    $os_type = null;
                    if (stripos($cpe_string, 'windows') !== false) {
                        $os_type = 1;
                    } elseif (stripos($cpe_string, 'linux') !== false || stripos($cpe_string, 'ubuntu') !== false || stripos($cpe_string, 'centos') !== false) {
                        $os_type = 2;
                    }
                    $newCPE->os_type = $os_type;
                    $newCPE->select = 'add'; 
                    $newCPE->cpe_data_id = 0;
                    $newCPE->credentials_id = null;
                    $newCPE->remark = null;
                    $newCPE->save();

                    // Add to cve_assets table
                    $cve_assets_ref = \Modules\SiteSettings\Entities\cve_assets::where('site_id', $data['site_id'])->where('ref_cpe', $newCPE->id)->first();
                    if (!$cve_assets_ref) {
                        $cve_assets_ref = new \Modules\SiteSettings\Entities\cve_assets();
                        $cve_assets_ref->code = generator_uuid();
                    }
                    $cve_assets_ref->vendor = $newCPE->vendor;
                    $cve_assets_ref->title = $newCPE->title;
                    $cve_assets_ref->version = $newCPE->version;
                    $cve_assets_ref->edition = $newCPE->edition;
                    $site = \Modules\SiteSettings\Entities\SiteSettings::withTrashed()->where('id', $data['site_id'])->first();
                    $domain = \Modules\SiteSettings\Entities\Domain::withTrashed()->where('id', $data['domain_id'])->first();
                    $cve_assets_ref->Site = $site ? $site->name : '';
                    
                    // Get parent value for display (used for internal mapping keys)
                    $parentValue = $currentAsset->raw_data;
                    foreach($savedAssetsDataMap as $val => $sid) {
                        if($sid == $cpe_asset_id) {
                            $parentValue = $val;
                            break;
                        }
                    }
                    $cve_assets_ref->IP = $assetIP;
                    $cve_assets_ref->Hostname = $domain ? $domain->name : '';
                    $cve_assets_ref->site_id = $data['site_id'];
                    $cve_assets_ref->active = 1;
                    $cve_assets_ref->ref_cpe = $newCPE->id;
                    $cve_assets_ref->save();

                    $newCPE->ref_cve_assets = $cve_assets_ref->id;
                    $newCPE->save();

                    // Track this cve_asset_id for future mappings (like Direct CVEs)
                    $cveAssetIdMap[$parentValue] = $cve_assets_ref->id;

                    // 🔥 IMMEDIATE MAPPING for this new CPE 🔥
                    // Find CVEs in CveTemp that match this CPE's vendor and title and target IP/Host
                    $matchingCVEs = \App\TransactionScansCveTemp::where('site_id', $data['site_id'])
                        ->where('is_mapped', '!=', 1)
                        ->where(function ($query) use ($parentValue, $domain) {
                            $query->where('target', $parentValue);
                            if ($domain) {
                                $query->orWhere('target', $domain->name);
                            }
                        })
                        ->where(function ($query) use ($vendor_name, $product_name) {
                            $query->where('affected_cpe', 'like', "%:$vendor_name:$product_name:%")
                                  ->orWhere('cpe_uri', 'like', "%:$vendor_name:$product_name:%");
                        })
                        ->get();

                    foreach ($matchingCVEs as $cve_temp) {
                        $this->save_cve_mapping($cve_temp->namecve, $data['site_id'], $cve_assets_ref->id, $cve_temp);
                    }

                    $transaction_client_cpe = \App\transaction_client_cpe::where('site_id', $data['site_id'])->where('transaction_id', $newCPE->id)->first();
                    if($transaction_client_cpe){
                        $transaction_client_cpe->transaction_mode = 'insert';
                        $transaction_client_cpe->transaction_data_status = 1;
                        $transaction_client_cpe->status = 1;
                        $transaction_client_cpe->save();
                    }else{
                        $transaction_client_cpe = new \App\transaction_client_cpe();
                        $transaction_client_cpe->site_id = $data['site_id'];
                        $transaction_client_cpe->transaction_id = $newCPE->id;
                        $transaction_client_cpe->transaction_mode = 'insert';
                        $transaction_client_cpe->transaction_data_status = 1;
                        $transaction_client_cpe->status = 1;
                        $transaction_client_cpe->save();
                    }
                }






                if ($item['data_type'] == 13) {
                    // Record to assets_port table for structured display
                    // Use ip_address if available, otherwise fallback to raw_data_base
                    $portAssetName = (!empty($item['ip_address'])) ? $item['ip_address'] : $item['raw_data_base'];
                    
                    $newPort = Assets_port::where('site_id', $data['site_id'])
                        ->where('asset_name', $portAssetName)
                        ->where('port', $item['raw_data'])
                        ->first();
                    if (!$newPort) {
                        $newPort = new Assets_port();
                    }
                    $newPort->site_id = $data['site_id'];
                    $newPort->asset_name = $portAssetName;
                    $newPort->port = $item['raw_data'];
                    $newPort->status = 1;
                    $newPort->save();
                }

                if ($AssetsData) {
                    $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $data['site_id'])->where('transaction_id', $AssetsData->id)->first();
                    if($transaction_client_asset_data){
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }else{
                        $transaction_client_asset_data = new transaction_client_asset_data();
                        $transaction_client_asset_data -> site_id = $data['site_id'];
                        $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }
                }

                
                // ALWAYS update TransactionScans if the user submits it, recovering any stuck states
                $dataTypeValue = null;
                if ($AssetsData && $AssetsData->get_data_type) {
                    $dataTypeValue = $AssetsData->get_data_type->value;
                } else {
                    // Fallback lookup if not loaded or saving fresh
                    $dataTypeValue = \App\DataTypes::where('id', $item['data_type'])->value('value');
                }

                if ($dataTypeValue) {
                    $TransactionScans = TransactionScans::where('site_id', $data['site_id'])
                        ->where('domain_id', $data['domain_id'])
                        ->where('raw_data', $item['raw_data'])
                        ->whereRaw('LOWER(data_type) = LOWER(?)', [$dataTypeValue])
                        ->first();
                    
                    if ($TransactionScans && $TransactionScans->status_asset_use != 1) {
                        $TransactionScans->status_asset_use = 1;
                        $TransactionScans->save();
                    }
                }

                // Restore immediate mapping for Direct CVE (Datatype 16)
                if ($AssetsData && $item['data_type'] == 16) {
                    $mappingTarget = (!empty($item['ip_address'])) ? $item['ip_address'] : $item['raw_data_base'];
                    $cve_asset_id = isset($cveAssetIdMap[$mappingTarget]) ? $cveAssetIdMap[$mappingTarget] : null;

                    if (!$cve_asset_id) {
                        // Look up in database if not in current session map
                        $existingCveAsset = \Modules\SiteSettings\Entities\cve_assets::where('site_id', $data['site_id'])
                            ->where(function($q) use ($mappingTarget) {
                                $q->where('IP', $mappingTarget)->orWhere('Hostname', $mappingTarget);
                            })
                            ->first();
                        $cve_asset_id = $existingCveAsset ? $existingCveAsset->id : null;
                    }

                    if ($cve_asset_id) {
                        $this->save_cve_mapping($item['raw_data'], $data['site_id'], $cve_asset_id);
                    } else {
                        \Log::warning("[MAPPING] Could not find cve_assets record for Direct CVE: " . $item['raw_data'] . " on target: " . $mappingTarget);
                    }
                }
            }
        }

        // Catch-all Smart Sweep (moved outside all loops for performance)
        foreach ($request->assets as $data) {
            $site_data = \App\Entities\Sites::where('id', $data['site_id'])->first();
            $site_name = $site_data ? $site_data->name : '';
            $site_cve_assets = \DB::table('cve_assets')
                ->where(function($q) use ($site_name, $data) {
                    $q->where('site_id', $data['site_id']);
                    if ($site_name) {
                        $q->orWhere('Site', 'like', '%' . $site_name . '%');
                    }
                })
                ->where('active', 1)
                ->get();

            $cve_temps = \App\TransactionScansCveTemp::where('site_id', $data['site_id'])
                ->where('domain_id', $data['domain_id'])
                ->where('is_mapped', '!=', 1)
                ->get();
            
            if (count($cve_temps) > 0) {
                \Log::info("DEBUG: Catch-all sweep for " . count($cve_temps) . " unmapped CVEs");
                foreach ($cve_temps as $cve_temp) {
                    $matched_asset_ids = [];
                    $cpe_str = $cve_temp->affected_cpe ?: $cve_temp->cpe_uri;
                    $vendor = ''; $product = '';
                    if ($cpe_str) {
                        $parts = explode(':', $cpe_str);
                        $vendor = $parts[3] ?? '';
                        $product = $parts[4] ?? '';
                    }

                    foreach ($site_cve_assets as $asset) {
                        $ip_match = ($cve_temp->target && (strcasecmp($asset->IP, $cve_temp->target) == 0 || strcasecmp($asset->Hostname, $cve_temp->target) == 0));
                        if ($vendor && $product) {
                            if ($ip_match && strcasecmp($asset->vendor, $vendor) == 0 && strcasecmp($asset->title, $product) == 0) {
                                $matched_asset_ids[] = $asset->id;
                            }
                        } else {
                            if ($ip_match) {
                                $matched_asset_ids[] = $asset->id;
                            }
                        }
                    }

                    foreach (array_unique($matched_asset_ids) as $asset_id) {
                        $this->save_cve_mapping($cve_temp->namecve, $data['site_id'], $asset_id, $cve_temp);
                    }
                }
            }
        }
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => '']);
    }

    public function save_assets_new(Request $request)
    {
        
        foreach ($request->assets as $data) {
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();

            if (!$Assets) {
              
                $Assets = new Assets;
                $Assets->code = generator_uuid();
                $Assets->created_by = Auth::user()->id;
                $Assets->site_id = $data['site_id'];
                $Assets->domain_id = $data['domain_id'];
                $Assets->status = 1;
                $Assets->raw_data = $data['raw_data'];
                $Assets->save();

                $transaction_client_asset = transaction_client_asset::where('site_id', $data['site_id'])->where('transaction_id', $Assets->id)->first();
                if($transaction_client_asset){
                    $transaction_client_asset -> transaction_mode = 'insert';
                    $transaction_client_asset -> transaction_data_status = 1;
                    $transaction_client_asset -> status = 1;
                    $transaction_client_asset -> save();
                }else{
                    $transaction_client_asset = new transaction_client_asset();
                    $transaction_client_asset -> site_id = $data['site_id'];
                    $transaction_client_asset -> transaction_id = $Assets->id;
                    $transaction_client_asset -> transaction_mode = 'insert';
                    $transaction_client_asset -> transaction_data_status = 1;
                    $transaction_client_asset -> status = 1;
                    $transaction_client_asset -> save();
                }
            }
            $savedAssetsDataMap = [];
            $cveAssetIdMap = [];

            // Identify the actual IP Address for this asset to ensure "By IPs" view is correct
            $ipTypeId = \App\DataTypes::where('value', 'IP Address')->value('id');
            $assetIP = \Modules\Scans\Entities\AssetsData::where('asset_id', $Assets->id)
                ->where('data_type_id', $ipTypeId)
                ->value('value') ?: $Assets->raw_data;

            foreach ($request->assets_data as $item) {
                if (!$item || !isset($item['raw_data_base'])) continue;

                // If we are saving an IP Address in this request, update our assetIP
                if ($item['data_type'] == $ipTypeId) {
                    $assetIP = $item['raw_data'];
                }
                // --- SMART ASSET LINKING ---
                // If ip_address is provided, use it instead of the domain as the base for ports and other findings
                $mappingBase = (!empty($item['ip_address'])) ? $item['ip_address'] : $item['raw_data_base'];
                $currentAsset = Assets::where('raw_data', $mappingBase)->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
                if (!$currentAsset) {
                    $currentAsset = new Assets;
                    $currentAsset->code = generator_uuid();
                    $currentAsset->created_by = Auth::user()->id;
                    $currentAsset->site_id = $data['site_id'];
                    $currentAsset->domain_id = $data['domain_id'];
                    $currentAsset->status = 1;
                    $currentAsset->raw_data = $mappingBase;
                    $currentAsset->save();

                    $tr_asset = transaction_client_asset::where('site_id', $data['site_id'])->where('transaction_id', $currentAsset->id)->first();
                    if (!$tr_asset) {
                        $tr_asset = new transaction_client_asset();
                        $tr_asset->site_id = $data['site_id'];
                        $tr_asset->transaction_id = $currentAsset->id;
                    }
                    $tr_asset->transaction_mode = 'insert';
                    $tr_asset->transaction_data_status = 1;
                    $tr_asset->status = 1;
                    $tr_asset->save();
                }
                // --- END SMART ASSET LINKING ---

                // Ensure this Asset has an identity (Subdomain or IP) saved in assets_datas to display correctly in Host list
                $identityType = (filter_var($mappingBase, FILTER_VALIDATE_IP)) ? 5 : 14; 
                $assetIdentity = AssetsData::where('asset_id', $currentAsset->id)
                    ->where('value', $mappingBase)
                    ->where('data_type_id', $identityType)
                    ->where('site_id', $data['site_id'])
                    ->first();
                if (!$assetIdentity) {
                    $assetIdentity = new AssetsData;
                    $assetIdentity->code = generator_uuid();
                    $assetIdentity->created_by = Auth::user()->id;
                    $assetIdentity->site_id = $data['site_id'];
                    $assetIdentity->domain_id = $data['domain_id'];
                    $assetIdentity->status = 1;
                    $assetIdentity->value = $mappingBase;
                    $assetIdentity->data_type_id = $identityType;
                    $assetIdentity->asset_id = $currentAsset->id;
                    $assetIdentity->save();
                }


                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                    ->where('domain_id', $data['domain_id'])
                    ->where('value', $item['raw_data'])
                    ->where('data_type_id', $item['data_type'])
                    ->where('asset_id', $currentAsset->id)
                    ->first();
                
                if (!$AssetsData) {
                    // Always save if we have a valid parent (which we now do via Smart Asset Linking)
                    $AssetsData = new AssetsData;
                    $AssetsData->code = generator_uuid();
                    $AssetsData->created_by = Auth::user()->id;
                    $AssetsData->site_id = $data['site_id'];
                    $AssetsData->domain_id = $data['domain_id'];
                    $AssetsData->status = 1;
                    $AssetsData->value = $item['raw_data'];
                    $AssetsData->data_type_id = $item['data_type'];
                    $AssetsData->asset_id = $currentAsset->id;
                    
                    // ARCHITECTURAL IMPROVEMENT: Set parent relationship
                    // If it's not the identity itself (IP/Domain), link it to the identity
                    if ($item['raw_data'] != $mappingBase) {
                        $AssetsData->refer_asset_id = $assetIdentity->id;
                    }
                    
                    $AssetsData->save();
                }

                if ($AssetsData && $item['data_type'] != 17) {
                    $savedAssetsDataMap[$item['raw_data']] = $AssetsData->id;
                }

                if ($AssetsData && $item['data_type'] == 17) {
                    // Add to CPE and cve_assets tables if it's a CPE
                    $cpe_string = $item['raw_data'];
                    $cpe_parts = explode(':', $cpe_string);
                    $vendor_name = isset($cpe_parts[3]) ? $cpe_parts[3] : '';
                    $product_name = isset($cpe_parts[4]) ? $cpe_parts[4] : '';
                    $product_version = isset($cpe_parts[5]) ? $cpe_parts[5] : '*';
                    $product_edition = isset($cpe_parts[6]) ? $cpe_parts[6] : '*';

                    // Find the parent AssetsData (IP, Domain or Port) to link the CPE to
                    // BEST PRACTICE: Link to the assetIdentity (IP or Domain) for maximum visibility in the UI
                    $cpe_asset_id = $assetIdentity->id; 

                    $newCPE = \Modules\Scans\Entities\CPE::where('asset_id', $cpe_asset_id)->where('result', $cpe_string)->first();
                    if (!$newCPE) {
                        $newCPE = new \Modules\Scans\Entities\CPE();
                        $newCPE->code = generator_uuid();
                    }
                    $newCPE->asset_id = $cpe_asset_id; 
                    $newCPE->result = $cpe_string;
                    $newCPE->vendor = $vendor_name;
                    $newCPE->title = $product_name;
                    $newCPE->version = $product_version;
                    $newCPE->edition = $product_edition;
                    
                    $os_type = null;
                    if (stripos($cpe_string, 'windows') !== false) {
                        $os_type = 1;
                    } elseif (stripos($cpe_string, 'linux') !== false || stripos($cpe_string, 'ubuntu') !== false || stripos($cpe_string, 'centos') !== false) {
                        $os_type = 2;
                    }
                    $newCPE->os_type = $os_type;
                    $newCPE->select = 'add'; 
                    $newCPE->cpe_data_id = 0;
                    $newCPE->credentials_id = null;
                    $newCPE->remark = null;
                    $newCPE->save();

                    // Add to cve_assets table
                    $cve_assets_ref = \Modules\SiteSettings\Entities\cve_assets::where('site_id', $data['site_id'])->where('ref_cpe', $newCPE->id)->first();
                    if (!$cve_assets_ref) {
                        $cve_assets_ref = new \Modules\SiteSettings\Entities\cve_assets();
                        $cve_assets_ref->code = generator_uuid();
                    }
                    $cve_assets_ref->vendor = $newCPE->vendor;
                    $cve_assets_ref->title = $newCPE->title;
                    $cve_assets_ref->version = $newCPE->version;
                    $cve_assets_ref->edition = $newCPE->edition;
                    $site = \Modules\SiteSettings\Entities\SiteSettings::withTrashed()->where('id', $data['site_id'])->first();
                    $domain = \Modules\SiteSettings\Entities\Domain::withTrashed()->where('id', $data['domain_id'])->first();
                    $cve_assets_ref->Site = $site ? $site->name : '';
                    
                    $parentValue = $currentAsset->raw_data;
                    foreach($savedAssetsDataMap as $val => $sid) {
                        if($sid == $cpe_asset_id) {
                            $parentValue = $val;
                            break;
                        }
                    }
                    $cve_assets_ref->IP = $assetIP;
                    $cve_assets_ref->Hostname = $domain ? $domain->name : '';
                    $cve_assets_ref->site_id = $data['site_id'];
                    $cve_assets_ref->active = 1;
                    $cve_assets_ref->ref_cpe = $newCPE->id;
                    $cve_assets_ref->save();

                    $newCPE->ref_cve_assets = $cve_assets_ref->id;
                    $newCPE->save();

                    // Track this cve_asset_id for internal mapping
                    $cveAssetIdMap[$parentValue] = $cve_assets_ref->id;

                    // 🔥 IMMEDIATE MAPPING for this new CPE 🔥
                    $matchingCVEs = \App\TransactionScansCveTemp::where('site_id', $data['site_id'])
                        ->where('is_mapped', '!=', 1)
                        ->where(function ($query) use ($parentValue, $domain) {
                            $query->where('target', $parentValue);
                            if ($domain) {
                                $query->orWhere('target', $domain->name);
                            }
                        })
                        ->where(function ($query) use ($vendor_name, $product_name) {
                            $query->where('affected_cpe', 'like', "%:$vendor_name:$product_name:%")
                                  ->orWhere('cpe_uri', 'like', "%:$vendor_name:$product_name:%");
                        })
                        ->get();

                    foreach ($matchingCVEs as $cve_temp) {
                        $this->save_cve_mapping($cve_temp->namecve, $data['site_id'], $cve_assets_ref->id, $cve_temp);
                    }

                    $transaction_client_cpe = \App\transaction_client_cpe::where('site_id', $data['site_id'])->where('transaction_id', $newCPE->id)->first();
                    if($transaction_client_cpe){
                        $transaction_client_cpe->transaction_mode = 'insert';
                        $transaction_client_cpe->transaction_data_status = 1;
                        $transaction_client_cpe->status = 1;
                        $transaction_client_cpe->save();
                    }else{
                        $transaction_client_cpe = new \App\transaction_client_cpe();
                        $transaction_client_cpe->site_id = $data['site_id'];
                        $transaction_client_cpe->transaction_id = $newCPE->id;
                        $transaction_client_cpe->transaction_mode = 'insert';
                        $transaction_client_cpe->transaction_data_status = 1;
                        $transaction_client_cpe->status = 1;
                        $transaction_client_cpe->save();
                    }
                }






                if ($AssetsData) {
                    $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $data['site_id'])->where('transaction_id', $AssetsData->id)->first();
                    if($transaction_client_asset_data){
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }else{
                        $transaction_client_asset_data = new transaction_client_asset_data();
                        $transaction_client_asset_data -> site_id = $data['site_id'];
                        $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }
                }

                // ALWAYS update TransactionScans if the user submits it, recovering any stuck states
                $dataTypeValue = null;
                if ($AssetsData && $AssetsData->get_data_type) {
                    $dataTypeValue = $AssetsData->get_data_type->value;
                } else {
                    // Fallback lookup if not loaded or saving fresh
                    $dataTypeValue = \App\DataTypes::where('id', $item['data_type'])->value('value');
                }

                if ($dataTypeValue) {
                    $TransactionScans = TransactionScans::where('site_id', $data['site_id'])
                        ->where('domain_id', $data['domain_id'])
                        ->where('raw_data', $item['raw_data'])
                        ->whereRaw('LOWER(data_type) = LOWER(?)', [$dataTypeValue])
                        ->first();
                    
                    if ($TransactionScans && $TransactionScans->status_asset_use != 1) {
                        $TransactionScans->status_asset_use = 1;
                        $TransactionScans->save();
                    }
                }

                // Restore immediate mapping for Direct CVE (Datatype 16)
                if ($AssetsData && $item['data_type'] == 16) {
                    $mappingTarget = $item['raw_data_base'];
                    $cve_asset_id = isset($cveAssetIdMap[$mappingTarget]) ? $cveAssetIdMap[$mappingTarget] : null;

                    if (!$cve_asset_id) {
                        $existingCveAsset = \Modules\SiteSettings\Entities\cve_assets::where('site_id', $data['site_id'])
                            ->where(function($q) use ($mappingTarget) {
                                $q->where('IP', $mappingTarget)->orWhere('Hostname', $mappingTarget);
                            })
                            ->first();
                        $cve_asset_id = $existingCveAsset ? $existingCveAsset->id : null;
                    }

                    if ($cve_asset_id) {
                        $this->save_cve_mapping($item['raw_data'], $data['site_id'], $cve_asset_id);
                    } else {
                        \Log::warning("[MAPPING-NEW] Could not find cve_assets record for Direct CVE: " . $item['raw_data'] . " on target: " . $mappingTarget);
                    }
                }
            }
        }

        // Catch-all Smart Sweep (moved outside all loops for performance)
        foreach ($request->assets as $data) {
            $site_data = \App\Entities\Sites::where('id', $data['site_id'])->first();
            $site_name = $site_data ? $site_data->name : '';
            $site_cve_assets = \DB::table('cve_assets')
                ->where(function($q) use ($site_name, $data) {
                    $q->where('site_id', $data['site_id']);
                    if ($site_name) {
                        $q->orWhere('Site', 'like', '%' . $site_name . '%');
                    }
                })
                ->where('active', 1)
                ->get();

            $cve_temps = \App\TransactionScansCveTemp::where('site_id', $data['site_id'])
                ->where('domain_id', $data['domain_id'])
                ->where('is_mapped', '!=', 1)
                ->get();
            
            if (count($cve_temps) > 0) {
                \Log::info("DEBUG: Catch-all sweep for " . count($cve_temps) . " unmapped CVEs");
                foreach ($cve_temps as $cve_temp) {
                    $matched_asset_ids = [];
                    $cpe_str = $cve_temp->affected_cpe ?: $cve_temp->cpe_uri;
                    $vendor = ''; $product = '';
                    if ($cpe_str) {
                        $parts = explode(':', $cpe_str);
                        $vendor = $parts[3] ?? '';
                        $product = $parts[4] ?? '';
                    }

                    foreach ($site_cve_assets as $asset) {
                        $ip_match = ($cve_temp->target && (strcasecmp($asset->IP, $cve_temp->target) == 0 || strcasecmp($asset->Hostname, $cve_temp->target) == 0));
                        if ($vendor && $product) {
                            if ($ip_match && strcasecmp($asset->vendor, $vendor) == 0 && strcasecmp($asset->title, $product) == 0) {
                                $matched_asset_ids[] = $asset->id;
                            }
                        } else {
                            if ($ip_match) {
                                $matched_asset_ids[] = $asset->id;
                            }
                        }
                    }

                    foreach (array_unique($matched_asset_ids) as $asset_id) {
                        $this->save_cve_mapping($cve_temp->namecve, $data['site_id'], $asset_id, $cve_temp);
                    }
                }
            }
        }

        return response()->json(['message' => langapp('changes_saved_successful'), 'error' => '', 'status_code' => '200', 'data' => '']);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('scans::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('scans::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('scans::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function scan_command(Request $request)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
        foreach ($TransactionTimeStampScans as $TransactionTimeStampScan) {
            $path = public_path() . '/files/scans/' . $TransactionTimeStampScan->get_site->code . '/' . $TransactionTimeStampScan->get_domain->code;
            $array = explode("\n", file_get_contents($path . '/looking_for_subdomain.txt'));
            $arrays = [];
            $arrays_final = [];
            $arrays_last_final = [];
            foreach ($array as $item) {
                $arrays[] = explode("\t", $item);
            }
            foreach ($arrays as $data) {
                $arrays_final[] = $data;
            }
            foreach ($arrays_final as $item) {
                $arrays = [];
                foreach ($item as $data) {
                    if (!empty($data)) {
                        $arrays[] = trim($data);
                    }
                }
                $arrays_last_final[] = $arrays;
            }
            $arrays_last_final = array_filter($arrays_last_final);
            array_pop($arrays_last_final);
            foreach ($arrays_last_final as $item) {
                $TransactionScans = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                    ->where('domain_id', $TransactionTimeStampScan->domain_id)
                    ->where('module', $item[0])
                    ->where('data_type', $item[1])
                    ->where('raw_data', $item[2])
                    ->first();
                if (!empty($TransactionScans)) {
                    $TransactionScans->updated_at = Carbon::now();
                } else {
                    $CreateTransactionScans = new TransactionScans();
                    $CreateTransactionScans->code = Str::uuid()->toString();
                    $CreateTransactionScans->created_by = $TransactionTimeStampScan->created_by;
                    $CreateTransactionScans->site_id = $TransactionTimeStampScan->site_id;
                    $CreateTransactionScans->domain_id = $TransactionTimeStampScan->domain_id;
                    $CreateTransactionScans->module = $item[0];
                    $CreateTransactionScans->data_type = $item[1];
                    $CreateTransactionScans->raw_data = $item[2];
                    $CreateTransactionScans->status = 1;
                    $CreateTransactionScans->save();
                }
            }

            $TransactionTimeStampScan->progress = 3;
            $TransactionTimeStampScan->save();
        }
    }

    public function save_scan()
    {
        $array = explode("\n", file_get_contents(public_path() . '/files/scans/looking_for_subdomain.txt'));
        $arrays = [];
        $arrays_final = [];
        $arrays_last_final = [];
        foreach ($array as $item) {
            $arrays[] = explode("\t", $item);
        }
        foreach ($arrays as $data) {
            $arrays_final[] = $data;
        }
        foreach ($arrays_final as $item) {
            $arrays = [];
            foreach ($item as $data) {
                if (!empty($data)) {
                    $arrays[] = trim($data);
                }
            }
            $arrays_last_final[] = $arrays;
        }
        $arrays_last_final = array_filter($arrays_last_final);
        array_pop($arrays_last_final);
        dd($arrays_last_final);
    }

    public function tableData()
    {
        $site_id = '';
        $site_code = $this->request->site_code;
        // $site_code = 'a7b6ff37-30ec-4494-9527-93b0ccc51d56';
        $site_id_find = SiteSettings::where("code", $site_code)->first();
        if ($site_code) {
            $site_id = $site_id_find->id;
        }

        // $site_code = $this->request->site_code;
        // $site_id = $this->request->site_id;
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        // $model = $this->user->query();
        $model = TransactionTimeStampScans::query();
        $test = 1;
        if ($site_id) {
            $model->when(
                $test == 1,
                function ($q) use ($site_id) {
                    return $q->where('site_id', '=', $site_id);
                }
            );
        }

        // $model = TransactionTimeStampScans::query();
        return DataTables::eloquent($model)
            ->editColumn('chk', function (TransactionTimeStampScans $model) {
                return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('name', function (TransactionTimeStampScans $model) {
                return '<label>' . @$model->get_site->name . '</label>';
            })
            ->addColumn('domain', function (TransactionTimeStampScans $model) {
                return '<label>' . @$model->get_domain->name . '</label>';
            })
            ->addColumn('progress', function (TransactionTimeStampScans $model) {
                $html = '';
                $html = get_name_scan_status($model->progress, 'badg');
                return $html;
            })
            ->addColumn('action', function (TransactionTimeStampScans $model) {
                $html = '';
                $html .= "<div style='display: flex;'><a href='" . route('scans.index', ['tab' => 'overview', 'site_code' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs'>
                                <i class='far fa-eye'></i>
                            </a>";
                if (@$model->progress !== 3) {
                    $html .= "<a href='#' class='btn btn-" . get_option('theme_color') . " btn-xs' disabled>
                                    <i class='fas fa-redo'></i>
                                </a>";
                } else {
                    $html .= "<a href='" . route('get.scans.redo_process', ['id' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <i class='fas fa-redo'></i>
                                </a>";
                }

                if (@$model->progress !== 3) {
                    $html .= "<a href='' class='disabled btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                    </a>
                                    <a href='' class='disabled btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                    </a>";
                } else {
                    $html .= "<a href='' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                    </a>
                    <a href='' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                    </a>";
                }

                $html .= "</div>";
                return $html;
            })
            ->rawColumns(['chk', 'name', 'domain', 'progress', 'action'])
            ->toJson();
    }

    public function tableDataScans(Request $request)
    {
        $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
        $TransactionScans = TransactionScans::where('site_id', $SiteSettings->site_id)
            ->where('domain_id', $SiteSettings->domain_id)
            ->where('module','!=','sfp_citadel')
            ->orderBy('updated_at', 'desc') // Show latest first
            ->get();
        return DataTables::of($TransactionScans)
            ->editColumn('chk', function (TransactionScans $data) {
                $res = '';
                if ($data->status_asset_use == 1) {
                    $res .= '';
                } else {
                    $res .= '<label>
                        <input name="select[]" value="' . $data->raw_data . '" data-id="' . $data->id . '" data-domain="' . $data->domain_id . '" data-site="' . $data->site_id . '" data-type="' . $data->data_type . '" data-ip="' . ($data->ip_address ?? '') . '" data-referent="' . $data->referent . '" class="select-chk" type="checkbox" />
                        <span class="label-text"></span>
                    </label>';
                }
                return $res;
            })
            ->editColumn('raw_data', function (TransactionScans $data) {
                $fullText = $data->raw_data;
                if (strlen($fullText) > 50) {
                    $truncated = substr($fullText, 0, 47) . '...';
                    return '<span title="' . htmlspecialchars($fullText) . '" style="cursor: default;">' . htmlspecialchars($truncated) . '</span>';
                }
                return htmlspecialchars($fullText);
            })
            ->addColumn('source', function (TransactionScans $data) {
            return $data->source ?? '-';
        })
        ->addColumn('use', function (TransactionScans $data) {
                $res = '';
                if ($data->status_asset_use == 1) {
                    $res .= '<span class="badge badge-success">Used</span>';
                } else if ($data->status == 0) {
                    $res .= '<span class="badge badge-danger">Not Found</span>';
                } else if ($data->status == 1) {
                    $res .= '<span class="badge badge-warning" style="background-color: #ffc107;">Discovered</span>';
                } else if ($data->status == 2) {
                    $res .= '<span class="badge badge-primary" style="background-color: #3869d4;">New</span>&nbsp;';
                }

                if ($data->data_type == 'CVE') {
                    $res .= ' <button class="btn btn-xs btn-info" onclick="view_cve_details(\''.$data->domain_id.'\', \''.$data->site_id.'\')" title="View CVE Details"><i class="fas fa-search"></i></button>';
                }
                return $res;
            })
            ->rawColumns(['chk', 'use', 'raw_data'])
            ->toJson();
    }

    public function tableDataScanAssets(Request $request)
    {
        if($request -> menu == 'scan'){
            $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
            if($SiteSettings){
                $Assets = Assets::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->get();
            }else{
                $Assets = [];
            }
        }else if($request -> menu == 'site'){
            $site = SiteSettings::select('id')->where('code', $request->code)->first();
            $SiteSettings = TransactionTimeStampScans::select('domain_id')->where('site_id', $site->id)->get();
            if($SiteSettings){
                $domain_id = [];
                foreach($SiteSettings as $data){
                    $domain_id[] = $data -> domain_id;
                }
                $Assets = Assets::where('site_id', $site -> id)->whereIn('domain_id', $domain_id)->get();
            }else{
                $Assets = [];
            }
        }else if($request -> menu == 'system'){
            if($request -> site_id == 0){
                $Assets = Assets::all();
            }else{
                $Assets = Assets::where('site_id', $request -> site_id)->get();
            }
            
        }
        $menu = $request -> menu;
        
        return DataTables::of($Assets)
        ->addColumn('chk', function (Assets $data) {
            return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' . $data->code . '"><span class="label-text"></span></label>';
        })
        ->addColumn('site', function (Assets $data) {
            if($data->get_site){
                return $data->get_site->name;
            }else{
                return 'No Data';
            }
            
        })
        ->addColumn('assets', function (Assets $data) {
            return $data->raw_data;
        })
        ->addColumn('referent', function (Assets $data) {
            $res = '';
            $res .= '<ul class="asset-list-tb">';
            foreach ($data->get_assets_data as $item) {
                $res .= '<li>' . $item->value . '</li>';
            }
            $res .= '</ul>';
            return $res;
        })
        ->addColumn('os', function (Assets $data) {
            $res = '';
            return $res;
        })
        ->addColumn('cpe', function (Assets $data) {
            $res = '';
            $res .= '<a href="'.route("assets.assets_add_cpe",['id'=>$data->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">Add </a>';
            return $res;
        })
        ->addColumn('status', function (Assets $data) {
            $res = '';
            if ($data->status == 1) {
                $res .= '<span class="badge badge-success">Active</span>';
            } else {
                $res .= '<span class="badge badge-danger">Inactive</span>';
            }
            return $res;
        })
        ->addColumn('action', function (Assets $data) use ($menu) {
            $SiteSettings = TransactionTimeStampScans::select('code')->where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->first();
            return '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $data->code, "code" => @$SiteSettings->code, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                </a>
                <a href="' . route("scans_assets.delete", ["id" => $data->code, "code" => @$SiteSettings->code, "page" => $menu]) . '" class="btn btn-xs btn-danger m-xs" data-toggle="ajaxModal">
                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                </a>';
            })
        ->rawColumns(['chk', 'site', 'assets', 'referent','os','cpe', 'status', 'action'])
        ->toJson();
    }

    public function scans_assets_delete($id, $code, $page)
    {
        $Assets = Assets::where('code', $id)->first();
        $data['scans'] = $Assets;
        $data['code'] = $code;
        $data['page'] = $page;
        return view('scans::modal.delete')->with($data);
    }

    public function f_scans_assets_delete($id = null, $code, $page)
    {
        $Assets = Assets::find($id);
        $transaction_client_asset = transaction_client_asset::where('site_id', $Assets -> site_id)->where('transaction_id', $Assets->id)->first();
        if($transaction_client_asset){
            $transaction_client_asset -> transaction_mode = 'delete';
            $transaction_client_asset -> transaction_data_status = 1;
            $transaction_client_asset -> status = 1;
            $transaction_client_asset -> save();
        }else{
            $transaction_client_asset = new transaction_client_asset();
            $transaction_client_asset -> site_id = $Assets -> site_id;
            $transaction_client_asset -> transaction_id = $Assets->id;
            $transaction_client_asset -> transaction_mode = 'delete';
            $transaction_client_asset -> transaction_data_status = 1;
            $transaction_client_asset -> status = 1;
            $transaction_client_asset -> save();
        }
        $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first()->code;
        $AssetsData = AssetsData::where('asset_id', $id)->get();
        foreach ($AssetsData as $data) {
            $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $data->site_id)->where('transaction_id', $data->id)->first();
            if($transaction_client_asset_data){
                $transaction_client_asset_data -> transaction_mode = 'delete';
                $transaction_client_asset_data -> transaction_data_status = 1;
                $transaction_client_asset_data -> status = 1;
                $transaction_client_asset_data -> save();
            }else{
                $transaction_client_asset_data = new transaction_client_asset_data();
                $transaction_client_asset_data -> site_id = $data->site_id;
                $transaction_client_asset_data -> transaction_id = $data->id;
                $transaction_client_asset_data -> transaction_mode = 'delete';
                $transaction_client_asset_data -> transaction_data_status = 1;
                $transaction_client_asset_data -> status = 1;
                $transaction_client_asset_data -> save();
            }
            $TransactionScans = TransactionScans::where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->where('raw_data', $data->value)
                ->where('data_type', $data->get_data_type->value)->first();
            if ($TransactionScans) {
                $TransactionScans->status_asset_use = 0;
                $TransactionScans->save();
            }
            $data->delete();
        }
        $Assets->delete();

        if($page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($page == 'scan'){
            return ajaxResponse(
                [
                    'message' => langapp('deleted_successfully'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function delete_assets_select(Request $request){
        $site = null;
        $SiteSettings = null;
        foreach($request -> asset_id as $key => $id){
            $Assets = Assets::where('code', $id)->first();
            $transaction_client_asset = transaction_client_asset::where('site_id', $Assets -> site_id)->where('transaction_id', $Assets->id)->first();
            if($transaction_client_asset){
                $transaction_client_asset -> transaction_mode = 'delete';
                $transaction_client_asset -> transaction_data_status = 1;
                $transaction_client_asset -> status = 1;
                $transaction_client_asset -> save();
            }else{
                $transaction_client_asset = new transaction_client_asset();
                $transaction_client_asset -> site_id = $Assets -> site_id;
                $transaction_client_asset -> transaction_id = $Assets->id;
                $transaction_client_asset -> transaction_mode = 'delete';
                $transaction_client_asset -> transaction_data_status = 1;
                $transaction_client_asset -> status = 1;
                $transaction_client_asset -> save();
            }
            if($key == 0){
                $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first();
            }
            $AssetsData = AssetsData::where('asset_id', $Assets -> id)->get();
            foreach ($AssetsData as $key_2 => $data) {
                $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $data->site_id)->where('transaction_id', $data->id)->first();
                if($transaction_client_asset_data){
                    $transaction_client_asset_data -> transaction_mode = 'delete';
                    $transaction_client_asset_data -> transaction_data_status = 1;
                    $transaction_client_asset_data -> status = 1;
                    $transaction_client_asset_data -> save();
                }else{
                    $transaction_client_asset_data = new transaction_client_asset_data();
                    $transaction_client_asset_data -> site_id = $data->site_id;
                    $transaction_client_asset_data -> transaction_id = $data->id;
                    $transaction_client_asset_data -> transaction_mode = 'delete';
                    $transaction_client_asset_data -> transaction_data_status = 1;
                    $transaction_client_asset_data -> status = 1;
                    $transaction_client_asset_data -> save();
                }
                if($key_2 == 0){
                    $SiteSettings = TransactionTimeStampScans::select('code')->where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->first();
                }
                $TransactionScans = TransactionScans::where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->where('raw_data', $data->value)
                    ->where('data_type', $data->get_data_type->value)->first();
                if ($TransactionScans) {
                    $TransactionScans->status_asset_use = 0;
                    $TransactionScans->save();
                }
                $data->delete();
            }
            $Assets->delete();
        }
        if($request -> page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'scan'){
            return ajaxResponse(
                [
                    'message' => langapp('deleted_successfully'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $SiteSettings -> code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function scans_redo($code)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $code)->first();
        $data['domain'] = $TransactionTimeStampScans;
        return view('scans::modal.redo_domain_transaction')->with($data);
    }

    public function redo_process($code)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $code)->first();
        if (!$TransactionTimeStampScans) {
            return ajaxResponse(['message' => "Record not found"], false, Response::HTTP_NOT_FOUND);
        }

        // 1. Update status to 'scanning'
        Log::debug("Manual Scan Triggered for record code: {$code}, Domain ID: {$TransactionTimeStampScans->domain_id}");
        $TransactionTimeStampScans->progress = 1; 
        $TransactionTimeStampScans->created_at = now();
        $TransactionTimeStampScans->save();

        // 2. Prepare to catch command output
        $output = new BufferedOutput;

        try {
            Log::info("Manual Scan Starting: Artisan call app:DomainScan for Domain ID: {$TransactionTimeStampScans->domain_id}");
            // 3. Execute DomainScan command
            // We use the domain_id from the record
            Artisan::call('app:DomainScan', [
                'domain_id' => $TransactionTimeStampScans->domain_id,
                '--save'    => true
            ], $output);

            // 4. Update status to 'complete'
            $TransactionTimeStampScans->progress = 3;
            $TransactionTimeStampScans->updated_at = now();
            $TransactionTimeStampScans->save();

            return ajaxResponse(
                [
                    'message' => "Scan Completed Successfully",
                    'redirect' => route('scans.index', ['tab' => 'overview', 'site_code' => $code]),
                    'output'   => $output->fetch()
                ],
                true,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            Log::error("Manual Scan Error: " . $e->getMessage());
            
            // Revert status to 'complete' (or whatever it was) if it fails? 
            // Or maybe mark as error? For now just keep it as 'complete' or original
            $TransactionTimeStampScans->progress = 3; 
            $TransactionTimeStampScans->save();

            return ajaxResponse(
                [
                    'message' => "Scan Failed: " . $e->getMessage(),
                    'redirect' => route('scans.index', ['tab' => 'overview', 'site_code' => $code]),
                ],
                false,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function scans_assets_edit_modal($id, $code, $page)
    {
        $Assets = Assets::where('code', $id)->first();
        $data['AssetsData'] = AssetsData::where('asset_id', $Assets->id)->get();
        $data['scans'] = $Assets;
        $data['code'] = $code;
        $data['code_asset'] = $id;
        $SiteSettings = TransactionTimeStampScans::where('code', $code)->first();
        $data['site'] = $SiteSettings;
        $data['page'] = $page;
        $data['DataTypes'] = DataTypes::all();
        return view('scans::modal.update_asset')->with($data);
    }

    public function scans_assets_edit(Request $request)
    {
        
   
        $Assets = Assets::where('code', $request->code_assets)->first();
        
        $Assets_id = $Assets->id;
        $arr= [];
        $site = SiteSettings::select('code')->withTrashed()->where('id', $Assets -> site_id)->first();
        // $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first();
        $site = $site->code;
        $Assets->raw_data = $request->assets[0]['raw_data'];
        $Assets->save();
        

        foreach ($request->assets_data as $data) {
            $myArray = explode(',', $data['data_type']);
            

            if ($myArray[0] != 13) { // NEW: Only process if not a port
                if (!empty($myArray[1])) {
                    $AssetsData = AssetsData::where('id', $myArray[1])->first();
                    $AssetsData->value = $data['raw_data'];
                    $AssetsData->data_type_id = $myArray[0];
                    $AssetsData->save();
                    $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $AssetsData->site_id)->where('transaction_id', $AssetsData->id)->first();
                    if($transaction_client_asset_data){
                        $transaction_client_asset_data -> transaction_mode = 'update';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }else{
                        $transaction_client_asset_data = new transaction_client_asset_data();
                        $transaction_client_asset_data -> site_id = $AssetsData->site_id;
                        $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                        $transaction_client_asset_data -> transaction_mode = 'update';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }
                } else {
                
                    $AssetsData = new AssetsData;
                    $AssetsData->code = generator_uuid();
                    $AssetsData -> created_by = Auth::user()->id;
                    $AssetsData->site_id = $request->assets[0]['site_id'];
                    $AssetsData->domain_id = $request->assets[0]['domain_id'];
                    $AssetsData->status = 1;
                    $AssetsData->value = $data['raw_data'];
                    $AssetsData->data_type_id = $myArray[0];
                    $AssetsData->asset_id = $Assets_id;
                    $AssetsData->save();
                    $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $AssetsData->site_id)->where('transaction_id', $AssetsData->id)->first();
                    if($transaction_client_asset_data){
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }else{
                        $transaction_client_asset_data = new transaction_client_asset_data();
                        $transaction_client_asset_data -> site_id = $AssetsData->site_id;
                        $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                        $transaction_client_asset_data -> transaction_mode = 'insert';
                        $transaction_client_asset_data -> transaction_data_status = 1;
                        $transaction_client_asset_data -> status = 1;
                        $transaction_client_asset_data -> save();
                    }
                }
                array_push($arr, $AssetsData->id);
            } else {
                // If it's a port, delete it from AssetsData if it was there before (cleaning up)
                if (!empty($myArray[1])) {
                    $AssetsData = AssetsData::where('id', $myArray[1])->first();
                    if ($AssetsData) {
                        $AssetsData->delete();
                    }
                }
            }
            if ($myArray[0] == 13) {
                // Record to assets_port table for structured display
                $newPort = Assets_port::where('site_id', $Assets->site_id)
                    ->where('asset_name', $Assets->raw_data)
                    ->where('port', $data['raw_data'])
                    ->first();
                if (!$newPort) {
                    $newPort = new Assets_port();
                }
                $newPort->site_id = $Assets->site_id;
                $newPort->asset_name = $Assets->raw_data;
                $newPort->port = $data['raw_data'];
                $newPort->status = 1;
                $newPort->save();
            }
        }

        
        
        $AssetsDataIsNot = AssetsData::where('asset_id', $Assets_id)->whereNotIn('id',$arr)->get();

        foreach($AssetsDataIsNot as $AssetsData){
            $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $AssetsData->site_id)->where('transaction_id', $AssetsData->id)->first();
            if($transaction_client_asset_data){
                $transaction_client_asset_data -> transaction_mode = 'delete';
                $transaction_client_asset_data -> transaction_data_status = 1;
                $transaction_client_asset_data -> status = 1;
                $transaction_client_asset_data -> save();
            }else{
                $transaction_client_asset_data = new transaction_client_asset_data();
                $transaction_client_asset_data -> site_id = $AssetsData->site_id;
                $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                $transaction_client_asset_data -> transaction_mode = 'delete';
                $transaction_client_asset_data -> transaction_data_status = 1;
                $transaction_client_asset_data -> status = 1;
                $transaction_client_asset_data -> save();
            }
            $AssetsData -> delete();
        }
        
        if($request -> page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'scan'){
            $TransactionTimeStampScansfor = TransactionTimeStampScans::where('domain_id',  $Assets->domain_id)->first();
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $TransactionTimeStampScansfor->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'system'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assets.index'),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'setting'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assets.index_setting'),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function get_cve_details(Request $request)
    {
        $site_id = $request->site_id;
        $domain_id = $request->domain_id;

        $cve_details = \App\TransactionScansCveTemp::where('site_id', $site_id)
            ->where('domain_id', $domain_id)
            ->get();

        return response()->json(['status' => 'success', 'data' => $cve_details]);
    }

    private function save_cve_mapping($cve_name, $site_id, $cve_asset_id, $cve_temp = null)
    {
        if (!$cve_temp) {
            $cve_temp = \App\TransactionScansCveTemp::where('namecve', $cve_name)
                ->where('site_id', $site_id)
                ->first();
        }

        if (!$cve_temp) return false;

        \Log::info("[MAPPING] Linking {$cve_name} to cve_asset {$cve_asset_id} (Site: {$site_id})");

        \DB::table('data_datacve')->updateOrInsert(
            ['namecve' => $cve_name],
            [
                'published' => $cve_temp->published,
                'modified' => $cve_temp->modified,
                'description' => $cve_temp->description,
                'cvss_score' => $cve_temp->cvss_score,
                'severity' => $cve_temp->severity,
                'updated_at' => now(),
                'created_at' => \DB::raw('IFNULL(created_at, NOW())')
            ]
        );

        $datacve_record = \DB::table('data_datacve')->where('namecve', $cve_name)->first();
        $datacve_id = $datacve_record ? $datacve_record->id : 0;

        \DB::table('data_cve_sources')->updateOrInsert(
            ['namecve' => $cve_name, 'source' => 'online'],
            [
                'datacve_id' => $datacve_id,
                'updated_at' => now(),
                'created_at' => \DB::raw('IFNULL(created_at, NOW())')
            ]
        );

        \DB::table('data_datacve_mapping')->updateOrInsert(
            ['namecve' => $cve_name, 'site_id' => $site_id],
            [
                'cveven_id' => $cve_asset_id,
                'published' => $cve_temp->published,
                'modified' => $cve_temp->modified,
                'description' => $cve_temp->description,
                'cvss_score' => $cve_temp->cvss_score,
                'severity' => $cve_temp->severity,
                'updated_at' => now(),
                'created_at' => \DB::raw('IFNULL(created_at, NOW())')
            ]
        );

        \DB::table('data_datacve_mapping_assets')->updateOrInsert(
            [
                'namecve' => $cve_name,
                'site_id' => $site_id,
                'cve_asset_id' => $cve_asset_id
            ],
            [
                'code' => (string) \Illuminate\Support\Str::uuid(),
                'updated_at' => now(),
                'created_at' => \DB::raw('IFNULL(created_at, NOW())')
            ]
        );

        \DB::table('transaction_client_data_datacve_mapping')->updateOrInsert(
            [
                'site_id' => $site_id,
                'transaction_id' => $cve_name
            ],
            [
                'transaction_mode' => 'insert',
                'transaction_data_status' => 1,
                'status' => 1,
                'updated_at' => now(),
                'created_at' => \DB::raw('IFNULL(created_at, NOW())')
            ]
        );

        $cve_temp->is_mapped = 1;
        return $cve_temp->save();
    }

}
