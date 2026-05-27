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

            if (!empty($data['ids'])) {
                // If specific IDs are provided (from a grouped table row), use them strictly
                // This ensures the modal only shows data that was actually in the selected row
                $ids = is_array($data['ids']) ? $data['ids'] : explode(',', $data['ids']);
                $query->whereIn('id', $ids);
            } else if (count($roots) > 0) {
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
                
                // Fix: Only process data belonging to the current asset in the loop
                if ($item['raw_data_base'] != $data['raw_data']) continue;

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
                    // Fix: Use raw_data_base as the hostname if it's not an IP, otherwise fallback to domain
                    $cve_assets_ref->Hostname = (!filter_var($item['raw_data_base'], FILTER_VALIDATE_IP)) ? $item['raw_data_base'] : ($domain ? $domain->domain : '');
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
                                $query->orWhere('target', $domain->domain);
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

        // --- NEW: STRICT STATUS UPDATE BY IDs ---
        // If specific record IDs were passed from the table rows, mark them all as used
        if ($request->has('ids') && is_array($request->ids)) {
            TransactionScans::whereIn('id', $request->ids)->update(['status_asset_use' => 1]);
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

                // Fix: Only process data belonging to the current asset based on index
                if ($item['raw_data_base'] != $data['raw_data_base']) continue;

                // If we are saving an IP Address in this request, update our assetIP
                if ($item['data_type'] == $ipTypeId) {
                    $assetIP = $item['raw_data'];
                }
                
                // --- SMART ASSET LINKING ---
                // For manual add, we use the asset hostname as the primary base
                $mappingBase = $data['raw_data']; 
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
                    // Fix: Use raw_data_base as the hostname if it's not an IP, otherwise fallback to domain
                    $cve_assets_ref->Hostname = (!filter_var($item['raw_data_base'], FILTER_VALIDATE_IP)) ? $item['raw_data_base'] : ($domain ? $domain->domain : '');
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
                                $query->orWhere('target', $domain->domain);
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
        if (!$SiteSettings) return DataTables::of(collect([]))->toJson();

        $allScans = TransactionScans::where('site_id', $SiteSettings->site_id)
            ->where('domain_id', $SiteSettings->domain_id)
            ->where('module', '!=', 'sfp_citadel')
            ->get();

        // --- Group by referent, then sub-group by IP ---

        // Step 1: Group all records by referent
        $byReferent = [];
        $noReferent = [];
        foreach ($allScans as $scan) {
            if ($scan->referent) {
                $byReferent[$scan->referent][] = $scan;
            } else {
                $noReferent[] = $scan;
            }
        }

        // Step 2: Find natural anchors (a record whose raw_data is a referent key)
        $anchorMap = [];
        foreach ($allScans as $scan) {
            if (isset($byReferent[$scan->raw_data]) && !isset($anchorMap[$scan->raw_data])) {
                $anchorMap[$scan->raw_data] = $scan;
            }
        }
        // Preload CVE -> CPE mappings for the scans in this request
        $cveNames = [];
        foreach ($allScans as $s) {
            if (in_array($s->data_type, ['CVE', 'Vulnerability'])) {
                $cveNames[] = $s->raw_data;
            }
        }
        $cveToCpe = [];
        if (!empty($cveNames)) {
            $cveTemps = \App\TransactionScansCveTemp::whereIn('namecve', array_unique($cveNames))->get();
            foreach ($cveTemps as $ct) {
                $cpe = $ct->cpe_uri ?: $ct->affected_cpe;
                if ($cpe) {
                    $cveToCpe[$ct->namecve][] = $cpe;
                }
            }
        }

        // Step 3: Process each referent group → build rows
        $rows = collect();
        $processedReferents = [];
        $processedIds = [];

        foreach ($byReferent as $ref => $records) {
            if (in_array($ref, $processedReferents)) continue;
            $processedReferents[] = $ref;

            // Find all records that "are" this domain (raw_data == ref)
            $rootRecords = $allScans->where('raw_data', $ref)->all();
            foreach ($rootRecords as $rr) $processedIds[] = $rr->id;
            
            // Primary anchor for ID/Site references
            $anchor = $anchorMap[$ref] ?? (reset($rootRecords) ?: null);
            $domainName = $ref;
            $parentReferent = $anchor ? ($anchor->referent ?: $ref) : $ref;
            $groupKey = $parentReferent . '||' . $domainName;

            // Sub-group children by IP
            $byIp = [];
            $noIp = [];
            foreach ($records as $child) {
                $processedIds[] = $child->id;
                $ip = $child->ip_address;
                if (!$ip && stripos($child->data_type, 'IP') !== false) {
                    $ip = $child->raw_data;
                }
                if ($ip) {
                    $byIp[$ip][] = $child;
                } else {
                    $noIp[] = $child;
                }
            }

            // Also pull records that reference each IP as their referent (CPE/CVE → IP)
            // And track all records in this family for the absolute latest date
            $allFamilyRecords = array_merge($rootRecords, $noIp);
            foreach ($byIp as $ip => $ipChildren) {
                $allFamilyRecords = array_merge($allFamilyRecords, $ipChildren);
            }

            foreach (array_keys($byIp) as $ip) {
                if (isset($byReferent[$ip]) && !in_array($ip, $processedReferents)) {
                    foreach ($byReferent[$ip] as $ipChild) {
                        $byIp[$ip][] = $ipChild;
                        $allFamilyRecords[] = $ipChild;
                        $processedIds[] = $ipChild->id;
                    }
                    $processedReferents[] = $ip;
                }
            }

            // Distribute $noIp CVEs to IPs if they match CPE
            $ipCpes = [];
            foreach ($byIp as $ip => $ipChildren) {
                foreach ($ipChildren as $child) {
                    if ($child->data_type == 'CPE') {
                        $ipCpes[$ip][] = $child->raw_data;
                    }
                }
            }

            $distributedNoIpIds = [];
            foreach ($noIp as $idx => $child) {
                if (in_array($child->data_type, ['CVE', 'Vulnerability'])) {
                    $cveName = $child->raw_data;
                    if (isset($cveToCpe[$cveName])) {
                        $reqCpes = $cveToCpe[$cveName];
                        foreach ($byIp as $ip => $ipChildren) {
                            $myCpes = $ipCpes[$ip] ?? [];
                            if (!empty(array_intersect($reqCpes, $myCpes))) {
                                $byIp[$ip][] = $child;
                                $distributedNoIpIds[] = $child->id;
                            }
                        }
                    }
                }
            }

            $finalNoIp = [];
            foreach ($noIp as $child) {
                if (!in_array($child->id, $distributedNoIpIds)) {
                    $finalNoIp[] = $child;
                }
            }

            // Calculate absolute latest date for the whole family
            $overallLatest = null;
            foreach ($allFamilyRecords as $fr) {
                if ($fr->updated_at && (!$overallLatest || $fr->updated_at > $overallLatest)) {
                    $overallLatest = $fr->updated_at;
                }
            }
            $overallLatestStr = $overallLatest ? $overallLatest->format('Y-m-d H:i:s') : '-';

            // Determine "best" Data Type for the group (Identity only)
            $identityPriority = ['Domain Name', 'Internet Domain Name', 'Domain', 'Subdomain', 'Sub Domain', 'Host', 'Internet Name'];
            
            $bestType = '-';
            foreach ($allFamilyRecords as $fr) {
                $currentFrType = $fr->data_type;
                if (in_array($currentFrType, $identityPriority)) {
                    if ($bestType == '-' || array_search($currentFrType, $identityPriority) < array_search($bestType, $identityPriority)) {
                        $bestType = $currentFrType;
                    }
                }
            }

            // Map results to standard display names
            if ($bestType == 'Internet Name' || ($bestType == '-' && $domainName != '-')) {
                $dataType = 'Domain';
            } else {
                $dataType = ($bestType != '-') ? $bestType : 'Domain';
            }

            // Build rows
            $fam = ['data_type' => $dataType, 'referent' => $parentReferent];

            if (empty($byIp)) {
                $r = $this->buildRow($anchor, $domainName, '-', $finalNoIp, $fam, $groupKey);
                $r['updated_at_str'] = $overallLatestStr;
                $r['source_str'] = !empty($r['source']) ? implode(', ', $r['source']) : '-';
                $rows->push((object)$r);
            } else {
                foreach ($byIp as $ip => $ipChildren) {
                    $r = $this->buildRow($anchor, $domainName, $ip, $ipChildren, $fam, $groupKey);
                    $r['updated_at_str'] = $overallLatestStr;
                    $r['source_str'] = !empty($r['source']) ? implode(', ', $r['source']) : '-';
                    $rows->push((object)$r);
                }
                if (!empty($finalNoIp)) {
                    $r = $this->buildRow($anchor, $domainName, '-', $finalNoIp, $fam, $groupKey);
                    $r['updated_at_str'] = $overallLatestStr;
                    $r['source_str'] = !empty($r['source']) ? implode(', ', $r['source']) : '-';
                    $rows->push((object)$r);
                }
            }
        }

        // Step 4: Orphan records (no referent at all)
        foreach ($noReferent as $scan) {
            if (in_array($scan->id, $processedIds)) continue;
            $ip = $scan->ip_address ?: (stripos($scan->data_type, 'IP') !== false ? $scan->raw_data : '-');
            $r = [
                'id' => $scan->id, 'ids' => [$scan->id],
                'domain' => $scan->raw_data, 'ip' => $ip,
                'ports' => [], 'networks' => [], 'cpes' => [], 'cves' => [],
                'data_type' => $scan->data_type,
                'referent' => '-',
                'updated_at' => $scan->updated_at,
                'updated_at_str' => $scan->updated_at ? $scan->updated_at->format('Y-m-d H:i:s') : '-',
                'source' => $scan->source ? [$scan->source] : [],
                'source_str' => $scan->source ?: '-',
                'status' => $scan->status,
                'status_asset_use' => $scan->status_asset_use,
                'site_id' => $scan->site_id, 'domain_id' => $scan->domain_id,
                'raw_data' => $scan->raw_data,
                'group_key' => 'orphan_' . $scan->id,
            ];
            $rows->push((object)$r);
        }

        // --- Sort by date (Newest to Oldest) ---
        $rows = $rows->sortByDesc('updated_at')->values();

        // --- DataTables Response ---
        return DataTables::of($rows)
            ->editColumn('chk', function ($data) {
                if ($data->status_asset_use == 1) return '';
                $ids_json = htmlspecialchars(json_encode($data->ids), ENT_QUOTES, 'UTF-8');
                return '<label><input name="select[]" value="'.$data->raw_data.'" data-ids=\''.$ids_json.'\' data-id="'.$data->id.'" data-domain="'.$data->domain_id.'" data-site="'.$data->site_id.'" data-type="'.$data->data_type.'" data-ip="'.($data->ip != '-' ? $data->ip : '').'" data-referent="'.$data->referent.'" class="select-chk" type="checkbox" /><span class="label-text"></span></label>';
            })
            ->addColumn('asset_html', function ($data) {
                $html = '<div class="asset-info-group" style="font-size:13px;line-height:1.7;">';
                $row = function($label, $value, $extra = '') {
                    return '<div style="margin-bottom:2px;"><span style="width:100px;display:inline-block;font-weight:700;color:#333;">'.$label.' : </span><span style="font-weight:400;color:#555;">'.$value.'</span>'.$extra.'</div>';
                };
                if ($data->domain != '-') {
                    $html .= $row('Domain', htmlspecialchars($data->domain));
                }
                if ($data->ip != '-') {
                    $html .= $row('IP', htmlspecialchars($data->ip));
                }
                if (!empty($data->ports)) {
                    sort($data->ports, SORT_NUMERIC);
                    $portsStr = htmlspecialchars(implode(', ', $data->ports));
                    $html .= '<div style="margin-bottom:2px; white-space: normal;"><span style="width:100px;display:inline-block;font-weight:700;color:#333;vertical-align:top;">Port : </span><span style="font-weight:400; color:#555; display:inline-block; width:calc(100% - 105px); word-wrap: break-word;">'.$portsStr.'</span></div>';
                }
                if (!empty($data->networks)) {
                    $html .= $row('Network', htmlspecialchars(implode(', ', array_unique($data->networks))));
                }
                foreach ($data->cpes as $cpe) {
                    $html .= $row('CPE', htmlspecialchars($cpe));
                }
                if (!empty($data->cves)) {
                    $cveList = htmlspecialchars(implode(', ', $data->cves));
                    $cveArrayJson = htmlspecialchars(json_encode(array_values(array_unique($data->cves))), ENT_QUOTES, 'UTF-8');
                    $btn = ' <button class="btn btn-xs btn-info" style="margin-left:10px; padding: 1px 8px;" onclick="view_cve_details(\''.$data->domain_id.'\', \''.$data->site_id.'\', this)" data-cves="'.$cveArrayJson.'" data-ip="'.$data->ip.'" data-domain="'.$data->domain.'" title="View CVE Details"><i class="fas fa-search"></i> View Detail</button>';
                    $html .= $row('CVE', $data->domain != '-' ? htmlspecialchars($data->domain) : $cveList, $btn);
                }
                $html .= '</div>';
                return $html;
            })
            ->editColumn('status', function ($data) {
                if ($data->status_asset_use == 1) {
                    return '<span class="badge badge-success">Used</span>';
                } else if ($data->status == 0) {
                    return '<span class="badge badge-danger">Not Found</span>';
                } else if ($data->status == 1) {
                    return '<span class="badge badge-warning" style="background-color:#ffc107;">Discovered</span>';
                } else if ($data->status == 2) {
                    return '<span class="badge badge-primary" style="background-color:#3869d4;">New</span>';
                }
                return '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('data_type', function ($data) {
                return $data->data_type ?: '-';
            })
            ->addColumn('referent', function ($data) {
                return $data->referent ?: '-';
            })
            ->editColumn('updated_at', function ($data) {
                return $data->updated_at_str ?? '-';
            })
            ->addColumn('source', function ($data) {
                return $data->source_str ?? '-';
            })
            ->addColumn('group_key', function ($data) {
                return $data->group_key;
            })
            ->rawColumns(['chk', 'asset_html', 'status'])
            ->make(true);
    }

    /**
     * Build a row array for a domain+IP combination.
     */
    private function buildRow($anchor, $domainName, $ip, $ipChildren, $fam, $groupKey)
    {
        $ports = []; $networks = []; $cpes = []; $cves = [];
        // Use anchor if available, otherwise use first child as base
        $base = $anchor ?? ($ipChildren[0] ?? null);
        $sources = ($base && $base->source) ? [$base->source] : [];
        $latestUpdate = $base ? $base->updated_at : null;
        $status = $base ? $base->status : 0;
        $statusUse = $base ? $base->status_asset_use : 0;
        $ids = $base ? [$base->id] : [];

        foreach ($ipChildren as $child) {
            if ($base && $child->id == $base->id && !$anchor) continue; // skip if already used as base
            $ids[] = $child->id;
            $this->classifyChild($child, $ports, $networks, $cpes, $cves);
            if ($child->source && !in_array($child->source, $sources)) $sources[] = $child->source;
            if ($child->updated_at > $latestUpdate) $latestUpdate = $child->updated_at;
            if ($child->status_asset_use != 1) $statusUse = 0;
            if ($child->status > $status) $status = $child->status;
        }

        return [
            'id' => $base ? $base->id : 0, 'ids' => $ids,
            'domain' => $domainName, 'ip' => $ip,
            'ports' => array_values(array_unique($ports)),
            'networks' => array_values(array_unique($networks)),
            'cpes' => array_values(array_unique($cpes)),
            'cves' => array_values(array_unique($cves)),
            'data_type' => $fam['data_type'],
            'referent' => $fam['referent'],
            'updated_at' => $latestUpdate,
            'source' => $sources,
            'status' => $status,
            'status_asset_use' => $statusUse,
            'site_id' => $base ? $base->site_id : 0,
            'domain_id' => $base ? $base->domain_id : 0,
            'raw_data' => $base ? $base->raw_data : $domainName,
            'group_key' => $groupKey,
        ];
    }

    /**
     * Classify a child record into the correct bucket.
     */
    private function classifyChild($child, &$ports, &$networks, &$cpes, &$cves)
    {
        $type = strtolower($child->data_type);
        if ($type == 'port') { $ports[] = $child->raw_data; }
        elseif ($type == 'network') { $networks[] = $child->raw_data; }
        elseif ($type == 'cpe') { $cpes[] = $child->raw_data; }
        elseif (in_array($type, ['cve', 'vulnerability'])) { $cves[] = $child->raw_data; }
    }

    /**
     * Merge a child record into an existing row array.
     */
    private function mergeChild(&$row, $child)
    {
        $row['ids'][] = $child->id;
        $ports = $row['ports']; $networks = $row['networks'];
        $cpes = $row['cpes']; $cves = $row['cves'];
        $this->classifyChild($child, $ports, $networks, $cpes, $cves);
        $row['ports'] = $ports; $row['networks'] = $networks;
        $row['cpes'] = $cpes; $row['cves'] = $cves;
        if ($child->source && !in_array($child->source, $row['source'])) $row['source'][] = $child->source;
        if ($child->updated_at > $row['updated_at']) $row['updated_at'] = $child->updated_at;
        if ($child->status_asset_use != 1) $row['status_asset_use'] = 0;
        if ($child->status > $row['status']) $row['status'] = $child->status;
    }

    public function tableDataScanAssets(Request $request)
    {
        $site_id = '';
        $code = $request->code;
        $SiteSettings = TransactionTimeStampScans::where('code', $code)->first();
        if ($code) {
            $site_id = $SiteSettings->site_id;
        }

        $model = Assets::query();
        if ($site_id) {
            $model->where('site_id', $site_id)->where('domain_id', $SiteSettings->domain_id);
        }

        return DataTables::eloquent($model)
            ->editColumn('chk', function (Assets $model) {
                return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('asset_html', function (Assets $model) {
                $html = '<div class="asset-info-container" style="font-size: 12px; line-height: 1.5;">';
                
                $row = function($label, $value, $isBold = false) {
                    $boldClass = $isBold ? 'font-bold' : '';
                    return '
                    <div style="display: flex; margin-bottom: 4px;">
                        <span class="text-muted font-bold" style="width: 110px; flex-shrink: 0; display: inline-block;">'.htmlspecialchars($label).':</span>
                        <span class="'.$boldClass.'" style="word-break: break-all;">'.$value.'</span>
                    </div>';
                };

                // Use Master record data
                $html .= $row('Asset', htmlspecialchars($model->raw_data), true);

                // Get details from AssetsData
                $details = AssetsData::where('asset_id', $model->id)->get();
                foreach ($details as $detail) {
                    $typeName = $detail->get_data_type ? $detail->get_data_type->value : 'Detail';
                    $html .= $row($typeName, htmlspecialchars($detail->value));
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('action', function (Assets $model) use ($code) {
                $html = '<div style="display: flex;">';
                $html .= '<a href="' . route('scans_assets.scans_assets_edit_modal', ['id' => $model->code, 'code' => $code, 'page' => 'scan']) . '" class="btn btn-' . get_option('theme_color') . ' btn-xs" data-toggle="ajaxModal"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg></a>';
                $html .= '<a href="' . route('scans_assets.delete', ['id' => $model->code, 'code' => $code, 'page' => 'scan']) . '" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg></a>';
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['chk', 'asset_html', 'action'])
            ->toJson();
    }

    public function scans_assets_delete($id, $code, $page)
    {
        $data['id'] = $id;
        $data['code'] = $code;
        $data['page'] = $page;
        return view('scans::modal.delete_asset')->with($data);
    }

    public function f_scans_assets_delete(Request $request)
    {
        $Assets = Assets::where('code', $request->id)->first();
        $Assets_id = $Assets->id;
        $AssetsData = AssetsData::where('asset_id', $Assets_id)->get();
        foreach ($AssetsData as $data) {
            $transaction_client_asset_data = transaction_client_asset_data::where('site_id', $data->site_id)->where('transaction_id', $data->id)->first();
            if ($transaction_client_asset_data) {
                $transaction_client_asset_data->transaction_mode = 'delete';
                $transaction_client_asset_data->transaction_data_status = 1;
                $transaction_client_asset_data->status = 1;
                $transaction_client_asset_data->save();
            } else {
                $transaction_client_asset_data = new transaction_client_asset_data();
                $transaction_client_asset_data->site_id = $data->site_id;
                $transaction_client_asset_data->transaction_id = $data->id;
                $transaction_client_asset_data->transaction_mode = 'delete';
                $transaction_client_asset_data->transaction_data_status = 1;
                $transaction_client_asset_data->status = 1;
                $transaction_client_asset_data->save();
            }
            $data->delete();
        }
        $transaction_client_asset = transaction_client_asset::where('site_id', $Assets->site_id)->where('transaction_id', $Assets->id)->first();
        if ($transaction_client_asset) {
            $transaction_client_asset->transaction_mode = 'delete';
            $transaction_client_asset->transaction_data_status = 1;
            $transaction_client_asset->status = 1;
            $transaction_client_asset->save();
        } else {
            $transaction_client_asset = new transaction_client_asset();
            $transaction_client_asset->site_id = $Assets->site_id;
            $transaction_client_asset->transaction_id = $Assets->id;
            $transaction_client_asset->transaction_mode = 'delete';
            $transaction_client_asset->transaction_data_status = 1;
            $transaction_client_asset->status = 1;
            $transaction_client_asset->save();
        }
        $Assets->delete();
        $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
        if($request -> page == 'site'){
            $site = SiteSettings::select('code')->withTrashed()->where('id', $Assets -> site_id)->first();
            return ajaxResponse(
                [
                    'message' => langapp('deleted_successfully'),
                    'redirect' => route('assetssite.index', ['id' => $site -> code]),
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
        $data['AssetsData'] = AssetsData::where('asset_id', $Assets->id)->where('data_type_id', '!=', 16)->get();
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

        
        
        $AssetsDataIsNot = AssetsData::where('asset_id', $Assets_id)->whereNotIn('id',$arr)->where('data_type_id', '!=', 16)->get();

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

        $query = \App\TransactionScansCveTemp::where('site_id', $site_id)
            ->where('domain_id', $domain_id);

        if ($request->has('cves') && is_array($request->cves) && count($request->cves) > 0) {
            $query->whereIn('namecve', $request->cves);
        }

        $cve_details = $query->get();

        // Optimization: Fetch all relevant records once to build maps
        $allSiteScans = \App\TransactionScans::where('site_id', $site_id)
            ->where('domain_id', $domain_id)
            ->whereIn('data_type', ['CVE', 'Vulnerability', 'CPE', 'Internet Name', 'Subdomain', 'Domain Name', 'Host'])
            ->get();

        $cpeToIps = [];
        $cveToIps = [];
        $cveToHosts = [];
        
        foreach ($allSiteScans as $scan) {
            $ip = $scan->ip_address;
            $host = $scan->referent;
            
            if ($scan->data_type == 'CPE') {
                $cpe = $scan->raw_data;
                if ($ip && $cpe) $cpeToIps[$cpe][] = $ip;
            }
            
            if (in_array($scan->data_type, ['CVE', 'Vulnerability'])) {
                $cveName = $scan->raw_data;
                if ($ip) $cveToIps[$cveName][] = $ip;
                if ($host) $cveToHosts[$cveName][] = $host;
            }
        }

        $reqIp = $request->ip;
        $reqDomain = $request->domain;

        // Enrich with asset info using the maps
        foreach ($cve_details as $cve) {
            $ips = $cveToIps[$cve->namecve] ?? [];
            $hosts = $cveToHosts[$cve->namecve] ?? [];
            
            // If the CVE temp record has a CPE, find all IPs that have that CPE
            $cpeUri = $cve->cpe_uri ?: $cve->affected_cpe;
            if ($cpeUri && isset($cpeToIps[$cpeUri])) {
                $ips = array_merge($ips, $cpeToIps[$cpeUri]);
            }

            $finalIps = array_unique($ips);
            $finalHosts = array_unique($hosts);

            // Context filtering: Only show the IP and Host of the row that was clicked
            if ($reqIp && $reqIp != '-') {
                $finalIps = [$reqIp];
            }
            if ($reqDomain && $reqDomain != '-') {
                $finalHosts = [$reqDomain];
            }

            $cve->aggregated_ips = implode(', ', $finalIps) ?: '-';
            $cve->aggregated_hosts = implode(', ', $finalHosts) ?: ($cve->target ?: '-');
        }

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
