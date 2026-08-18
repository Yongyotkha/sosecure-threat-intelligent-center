<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use App\Entities\fx_transaction_client_news_categories;
use App\LogEmail;
use App\Mail\NewsMail;
use App\siteNewsRelated;
use App\Topic;
use App\transaction_client_rss;
use App\TransactionClientNews;
use App\FXCategories;
use Modules\RSSFeedSettings\Http\Requests\CreateRssRequest;
use Auth;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\RSSFeedSettings\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\RSSFeedSettings\Entities\NewsTag;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use Modules\SiteSettings\Entities\SiteSettings;
use Yajra\DataTables\DataTables;


class RSSFeedSettingsController extends Controller
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

    public function __construct(Request $request, RSSData $RSSData)
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
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('rss_feed_settings');
        return view('rssfeedsettings::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('rssfeedsettings::create');
    }

    public function rss_data()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('rss_data');
        return view('rssfeedsettings::rss_data')->with($data);
    }

    public function ai_intel()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('news');
        return view('rssfeedsettings::ai_intel')->with($data);
    }

    public function tableAiIntel(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        $model = AiIntelNewsLog::with('get_rss_news');

        if (($request->keywords || $request->isDateSearch || $request->status) && $request->search_val == true) {
            if ($request->keywords) {
                $model->where(function ($q) use ($request) {
                    $q->where('title', 'LIKE', '%' . $request->keywords . '%')
                        ->orWhere('source', 'LIKE', '%' . $request->keywords . '%')
                        ->orWhere('executive_summary', 'LIKE', '%' . $request->keywords . '%');
                });
            }
            if ($request->isDateSearch) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;
                $date_start_date_format = date('Y-m-d', strtotime(@explode(' ', $date_start)[0]));
                $date_end_date_format = date('Y-m-d', strtotime(@explode(' ', $date_end)[0]));
                $model->whereBetween('published_at', [$date_start_date_format . ' 00:00:00', $date_end_date_format . ' 23:59:59']);
            }
            if ($request->status) {
                if ($request->status == '1') {
                    $model->where(function ($q) {
                        $q->where('status', 'promoted')
                            ->orWhereHas('get_rss_news');
                    });
                } else if ($request->status == '2') {
                    $model->where(function ($q) {
                        $q->where('status', '!=', 'promoted')
                            ->whereDoesntHave('get_rss_news');
                    });
                }
            }
        }

        return DataTables::of($model)->toJson();
    }

    public function ai_intel_create_news($code)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        $ai = AiIntelNewsLog::where('code', $code)->first();
        if (!$ai) {
            abort(404);
        }

        $transaction = null;
        if ($ai->transaction_rss_id) {
            $transaction = TransactionRssData::find($ai->transaction_rss_id);
        }

        if (!$transaction) {
            $published = $ai->published_at ? Carbon::parse($ai->published_at) : Carbon::now();
            $transaction = new TransactionRssData();
            $transaction->code = generator_uuid();
            $transaction->transaction_id = 2;
            $transaction->rss_id = null;
            $transaction->status = 1;
            $transaction->title = $ai->title;
            $transaction->link = $ai->source_url;
            $transaction->description = $this->buildAiIntelPlainSummary($ai);
            $transaction->enclosure = '';
            $transaction->isPermaLink = $ai->source_url;
            $transaction->pubDate = $published->format('Y-m-d');
            $transaction->transcation_date = $published->format('Y-m-d');
            $transaction->transcation_datetime = $published->format('Y-m-d H:i:s');
            $transaction->save();

            $ai->transaction_rss_id = $transaction->id;
            $ai->status = $ai->status === 'new' ? 'reviewed' : $ai->status;
            $ai->save();
        }

        $data['rss'] = $transaction;
        $data['RSSNews'] = '';
        $data['category'] = CategorySettings::where('active', 1)->get();
        $data['detail_default'] = $this->buildAiIntelDetailHtml($ai);
        $data['ai_intel_code'] = $ai->code;

        return view('rssfeedsettings::modal.create_news')->with($data);
    }

    protected function buildAiIntelPlainSummary(AiIntelNewsLog $ai)
    {
        $summary = $ai->executive_summary;
        if ($summary) {
            $decoded = json_decode($summary, true);
            if (is_array($decoded)) {
                return implode(' ', $decoded);
            }
            return strip_tags($summary);
        }
        return $ai->title;
    }

    protected function buildAiIntelDetailHtml(AiIntelNewsLog $ai)
    {
        $parts = [];

        $summary = $ai->executive_summary;
        $summaryItems = json_decode($summary, true);
        if (is_array($summaryItems)) {
            $parts[] = '<p><strong>สรุปข่าว (AI)</strong></p><ul>';
            foreach ($summaryItems as $item) {
                $parts[] = '<li>' . e($item) . '</li>';
            }
            $parts[] = '</ul>';
        } elseif (!empty($summary)) {
            $parts[] = '<p><strong>สรุปข่าว (AI)</strong></p><p>' . nl2br(e($summary)) . '</p>';
        }

        $context = $ai->intelligence_context;
        $contextData = json_decode($context, true);
        if (is_array($contextData)) {
            if (!empty($contextData['attacker_group'])) {
                $parts[] = '<p><strong>Threat Actor:</strong> ' . e($contextData['attacker_group']) . '</p>';
            }
            if (!empty($contextData['historical_narrative'])) {
                $parts[] = '<p><strong>บริบท:</strong> ' . e($contextData['historical_narrative']) . '</p>';
            }
        } elseif (!empty($context)) {
            $parts[] = '<p><strong>บริบท:</strong> ' . nl2br(e($context)) . '</p>';
        }

        $vulns = json_decode($ai->vulnerabilities_json, true);
        if (is_array($vulns) && count($vulns) > 0) {
            $parts[] = '<p><strong>CVE / Vulnerabilities</strong></p><ul>';
            foreach ($vulns as $v) {
                $cve = e(@$v['cve'] ?: @$v['id'] ?: '-');
                $product = e(@$v['product'] ?: '');
                $severity = e(@$v['severity'] ?: '');
                $parts[] = '<li>' . $cve . ($product ? ' — ' . $product : '') . ($severity ? ' (' . $severity . ')' : '') . '</li>';
            }
            $parts[] = '</ul>';
        }

        $iocs = json_decode($ai->indicators_json, true);
        if (is_array($iocs) && count($iocs) > 0) {
            $parts[] = '<p><strong>Indicators (IOC)</strong></p><ul>';
            foreach ($iocs as $ioc) {
                $type = e(@$ioc['type'] ?: '-');
                $value = e(@$ioc['value'] ?: '');
                $parts[] = '<li>[' . $type . '] ' . $value . '</li>';
            }
            $parts[] = '</ul>';
        }

        if ($ai->source_url) {
            $parts[] = '<p><strong>Source:</strong> <a href="' . e($ai->source_url) . '" target="_blank">' . e($ai->source_url) . '</a></p>';
        }

        return implode('', $parts);
    }

    public function ai_intel_delete(Request $request, $id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $model = AiIntelNewsLog::where('code', $id)->first();
        $data['ai_intel'] = $model;
        return view('rssfeedsettings::modal.ai_intel_delete')->with($data);
    }

    public function ai_intel_delete_process($id = null)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        AiIntelNewsLog::where('code', $id)->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.ai_intel'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function ai_intel_delete_checked(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        if (!empty($request->id)) {
            foreach ($request->id as $ai_id) {
                AiIntelNewsLog::where('id', $ai_id)->delete();
            }
        }
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.ai_intel'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function ai_intel_sync(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        try {
            $exitCode = Artisan::call('app:AI_Intel_News');
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sync failed. Check OPENAI_API_KEY and network access to RSS/OpenAI.',
                    'output' => $output,
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sync Intel completed.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('ai_intel_sync: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function tableRssData(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        // $columns = array(
        //     0 => 'id',
        //     1 => 'get_rss_source',
        //     2 => 'title',
        //     3 => 'description',
        //     4 => 'link',
        //     5 => 'pubDate',
        //     6 => 'pubDate',
        //     7 => 'code',
        // ); 
        // $order = $columns[$request->input('order.0.column')];
        // $dir = $request->input('order.0.dir');

        if (($request->keywords || $request->isDateSearch || $request->status) && $request->search_val == true) {
            $model = TransactionRssData::with('get_rss_news')->with('get_rss_source');

            if ($request->keywords) {
                $model->where('title', 'LIKE', '%' . $request->keywords . '%');
            }
            if ($request->isDateSearch) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                // $date_start_time_time = date("H:i", strtotime($date_start_time));
                // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start);

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                // $date_end_time_time = date("H:i", strtotime($date_end_time));
                // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model->whereBetween('transcation_date', array($date_start_date_format, $date_end_date_format));
            }
            if ($request->status) {

                if ($request->status == '1') {

                    $model = $model->whereHas('get_rss_news', function ($query) {
                        $query->where('transaction_rss_id', '!=', null);
                    });
                } else if ($request->status == '2') {

                    $model = $model->whereDoesntHave('get_rss_news', function ($query) {
                        $query->where('transaction_rss_id', '!=', null);
                    });
                }
            }

            // $model -> get();
        } else {
            $model = TransactionRssData::with('get_rss_news')->with('get_rss_source'); //->get()
        }

        return DataTables::of($model)->toJson();
        // ->editColumn('chk', function (TransactionRssData $model) {
        //         return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
        // })
        // ->addColumn('title', function (TransactionRssData $model) {


        //     if(@$model ->get_rss_news-> title_th){
        //         return '<div class="text-elip" data-rel="tooltip" title="'.@$model ->get_rss_news-> title_th.'">'.@$model ->get_rss_news -> title_th.'</div>';
        //     }else if(@$model ->get_rss_news-> title_en){
        //         return '<div class="text-elip" data-rel="tooltip" title="'.@$model ->get_rss_news-> title_en.'">'.@$model ->get_rss_news -> title_en.'</div>';
        //     }else if($model -> title){
        //         return '<div class="text-elip" data-rel="tooltip" title="'.$model -> title.'">'.$model -> title.'</div>';
        //     }else{
        //         return '-';
        //     }
        // })
        // ->addColumn('link', function (TransactionRssData $model) {
        //     $html = '';
        //     // $RSSNews = RSSNews::where('transaction_rss_id', $model -> id)->first();
        //     $word_leng = utf8_strlen($model->link);

        //     // if(!empty($RSSNews)){
        //     //     // if($word_leng > 30) {
        //     //     //     $html .= iconv_substr($model->link, 0, 30, "UTF-8");
        //     //     //     $html .= '...'; 
        //     //     // } else {
        //     //     //     $html .= $model->link; 
        //     //     // }
        //     //     $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
        //     // }else{
        //     //     // if($word_leng > 30) {
        //     //     //     $html .= iconv_substr($model->link, 0, 30, "UTF-8");
        //     //     //     $html .= '...'; 
        //     //     // } else {
        //     //     //     $html .= $model->link; 
        //     //     // }
        //     //     $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
        //     //     // $html .= iconv_substr($model->link, 0, 30, "UTF-8"); 
        //     //     // $html .= $word_leng; 


        //     if(!empty($model->get_rss_news)){
        //         if($word_leng > 30) {
        //             $html .= iconv_substr($model->link, 0, 30, "UTF-8");
        //             $html .= '...'; 
        //         } else {
        //             $html .= $model->link; 
        //         }
        //         $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
        //     }else{
        //         if($word_leng > 30) {
        //             $html .= iconv_substr($model->link, 0, 30, "UTF-8");
        //             $html .= '...'; 
        //         } else {
        //             $html .= $model->link; 
        //         }
        //         $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
        //         // $html .= iconv_substr($model->link, 0, 30, "UTF-8"); 
        //         // $html .= $word_leng; 
        //     }



        //     return $html;
        // })
        // ->addColumn('status', function (TransactionRssData $model) {
        //     $html = '';



        //     if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 1){
        //         $html .= '<span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
        //     }else if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 0){
        //         $html .= '<span class="badge badge-success">Used</span>';
        //     }else{
        //         $html .= '<span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
        //     }  
        //     return $html;
        // })
        // ->addColumn('action', function (TransactionRssData $model) {
        //     $html = '';
        //     $html_cr_news = '';

        //     if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 1){
        //         $html_cr_news .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
        //                                 <!--<svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>-->
        //                                 <i class='fas fa-share-square'></i>
        //                             </a>";
        //     }else if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 0){
        //         $html_cr_news .= "";
        //     }else{
        //         $html_cr_news .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
        //                             <!--<svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>-->
        //                             <i class='fas fa-share-square'></i>
        //                         </a>";
        //     }


        //     return $html;

        // })
        // ->rawColumns(['chk','title','link','status','action'])

    }

    public function deleteChecked(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        foreach ($request->id as $rss_id) {


            $data = TransactionRssData::where("id", $rss_id);
            $data->delete();
        }


        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    ///----------------
    public function tableNews(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        $model = new RSSNews();
        if (($request->keywords || $request->startDate || $request->endDate || $request->status_news || $request->news_source || $request->news_category) && $request->search_val == 1) {
            if ($request->keywords) {
                $model_where = RSSNews::where('title_en', 'LIKE', '%' . $request->keywords . '%')->first();

                if ($model_where) {
                    $model = $model->where('title_en', 'LIKE', '%' . $request->keywords . '%');
                } else {
                    $model = $model->where('title_th', 'LIKE', '%' . $request->keywords . '%');
                }
            }

            if ($request->isDateSearch == 1) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_datetime_format = Carbon::createFromFormat('Y-m-d h:i A', $date_start)->format('Y-m-d H:i:s');
                $date_end_datetime_format = Carbon::createFromFormat('Y-m-d h:i A', $date_end)->format('Y-m-d H:i:s');

                $model = $model->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format));
            }

            if ($request->status_news) {
                if ($request->status_news == 1 || $request->status_news == 2) {
                    if ($request->status_news == 1) {
                        $model = $model->where('save_draft', '=', 0);
                    } else if ($request->status_news == 2) {
                        $model = $model->where('save_draft', 1);

                    }
                }
            }

            if ($request->news_source) {

                $model = $model->whereIn('source', $request->news_source);
            }

            if ($request->news_category) {
                $news_cate_id = $request->news_category;

                $model = $model->whereHas('get_cate', function ($query) use ($news_cate_id) {
                    $query->whereIn('news_category_id', $news_cate_id);
                });
            }

            if ($request->status_serverity) {
                $model = $model->where('serverity', $request->status_serverity);
            }
        }

        return $model->orderBy('public_date', 'desc')->get();

        $model = $model->select(
            'id',
            'code',
            'title_th',
            'detail_th',
            'title_en',
            'detail_en',
            'source',
            'save_draft',
            'serverity',
            'public_date',
            'status',
            'created_at',
        )->orderBy('public_date', 'desc');

        return DataTables::of($model)
            ->editColumn('chk', function (RSSNews $model) {
                return '<label><input type="checkbox" name="checked" class="rss_new_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })

            ->addColumn('content_detail', function (RSSNews $model) {
                $html = '';
                $html .= '<div>';
                if ($model->title_th) {
                    $html .= '<a style="font-size:16px;" href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'th']) . '" target="_blank" data-rel="tooltip" title="' . $model->title_th . '">' . $model->title_th . '</a>';
                } else if ($model->title_en) {
                    $html .= '<a style="font-size:16px;" href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'en']) . '" target="_blank" data-rel="tooltip" title="' . $model->title_en . '">' . $model->title_en . '</a>';
                } else {
                    $html .= '<span style="font-size:16px;">No Title</span>';
                }
                $html .= '</div>';

                if ($model->source) {

                    $html .= '<span data-rel="tooltip" title="' . $model->source . '"><span class="m-r-5"><b>Source : </b>' . $model->source . '</span>';
                } else {
                    $html .= '<span data-rel="tooltip" title="None"><span class="m-r-5"><b>Source : </b> None</span>';
                }


                $html .= '<span class="text-trucate-ovf"> <span class="m-r-5 m-l-xs"><b>Category : </b>';
                $html .= '<span style="word-break: break-all;display: inline;" class="showmore">';
                if ($model->get_cate == "[]") {
                    $html .= 'None';
                } else {
                    $catagory_name = "";
                    $num = count($model->get_cate);
                    $i = 0;
                    foreach ($model->get_cate as $record) {
                        if (++$i === $num) {
                            $catagory_name .= $record->get_cate_name->name;
                        } else {
                            $catagory_name .= $record->get_cate_name->name . ",";
                        }
                    }
                    $htmls = '';

                    $htmls .= $catagory_name;
                    if ($htmls == 'None-delete0') {
                        $html .= str_replace('-delete0', '', $htmls);
                    } else {
                        $html .= str_replace('None-delete0', '', $htmls);
                    }
                }
                $html .= '<button class="btn-showmore btn-link text-primary" style="padding:0;padding-left:1px;padding-right:1px;" onclick="showMore(this)">ดูเพิ่มเติม</button></span>';


                $html .= '</span>';




                $html .= '<div>';

                $html .= ' <span class="m-r-5"><b>Public Date : </b>' . $model->public_date . '</span>';

                if ($model->serverity == 'critical') {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #b93624;">Critical</span></span>';
                } else if ($model->serverity == 'high') {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #fcc838;">High</span></span>';
                } else if ($model->serverity == 'medium') {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span></span>';
                } else if ($model->serverity == 'low') {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #88ce4f;">Low</span></span>';
                } else if ($model->serverity == 'information') {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #00dcff;">Information</span></span>';
                } else {
                    $html .= ' <span class="m-r-5"><b>Serverity : </b> - </span>';
                }


                if ($model->save_draft == 1) {
                    $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
                } else if ($model->save_draft == 0) {
                    $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-success">Public</span>';
                } else {
                    $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
                }

                $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
                $client = new MongoClient($DB_MONGO_KEY);
                if (app()->environment('local')) {
                    $collection_actor = $client->sosecure_threatintelligent_dev->fx_otx_adversaries;
                    $conn = $client->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                } else {
                    $collection_actor = $client->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                    $conn = $client->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                }

                $query_camp = [
                    'pulse_id' => $model->id,
                    'mode' => 'news',
                    'join' => 'campainge',
                    'delete_at' => null
                ];
                $option_camp = [];

                $final_camp = $conn->find($query_camp, $option_camp);
                $result_camp = $final_camp->toArray();
                $count_result_camp = count($result_camp);

                if ($count_result_camp > 0) {
                    $html .= ' <span class="m-r-md">
                            <b>Campainge : </b>';

                    $a_data_campainge = [];
                    foreach ($result_camp as $camp_data) {
                        $a_data_campainge[] = '<a href="/actor/campainge_detail?_id=' . $camp_data->adversary_uuid . '&mode=news">' . $camp_data->adversary_name . '</a>';
                    }

                    $html .= $a_data_campainge ? implode(", ", $a_data_campainge) : ' - ';

                    $html .= '</span> ';
                }


                $html .= '</div>';
                return $html;
            })

            ->addColumn('link', function (RSSNews $model) {
                $html = '';
                $html_th = '';
                $html_en = '';
                $html_line = '';
                if ($model->title_th) {
                    $html_th = '<a href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'th']) . '" target="_blank">TH</a>';
                }
                if ($model->title_en) {
                    $html_en = '<a href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'en']) . '" target="_blank">EN</a>';
                    $html_line = ' | ';
                }

                $html .= $html_th . $html_line . $html_en;

                return $html;
            })
            ->addColumn('category', function (RSSNews $model) {
                $html = '';
                $html .= '<label>' . $model->category . '</label>';
                return $html;
            })

            ->addColumn('status', function (RSSNews $model) {
                if ($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="news-active-' . $model->code . '" onchange="change_news_active(\'' . $model->code . '\')" ' . $checked_val . ' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function (RSSNews $model) {
                $html = '';

                $html .= "
                <a href='" . route('rssfeedsettings.rss_news_edit_news', ['code' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='" . route('rssfeedsettings.rss_news_delete', ['id' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
            })
            ->rawColumns(['chk', 'content_detail', 'category', 'serverity', 'actor', 'status', 'link', 'action'])
            ->toJson();

    }

    public function load_top_source(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        // ----------------------- old -----------------------

        // $model = New RSSNews();
        // if($request -> search_val == 1){

        //     if($request -> keywords){
        //         $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$request -> keywords.'%')->first();

        //         if($model_where) {
        //             $model = $model -> where('title_en', 'LIKE' ,'%'.$request -> keywords.'%');
        //         } else {
        //             $model = $model -> where('title_th', 'LIKE' ,'%'.$request -> keywords.'%');
        //         }

        //     }

        //     if($request -> isDateSearch == 1){
        //         $date_start = $request->startDate;
        //         $date_end = $request->endDate;

        //         $date_start_explode = explode(" ",$date_start);
        //         $date_start_date = @$date_start_explode[0];
        //         $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
        //         // dd($date_start_time);
        //         $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
        //         // dd($date_start_date_format);
        //         $date_start_time_time = date("H:i", strtotime($date_start_time));
        //         $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
        //         // dd($date_start);

        //         $date_end_explode = explode(" ",$date_end);
        //         $date_end_date = @$date_end_explode[0];
        //         $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
        //         // dd($date_end_time);
        //         $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        //         $date_end_time_time = date("H:i", strtotime($date_end_time));
        //         $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
        //         // dd($date_end_time_time);

        //         // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
        //         $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
        //     }

        //     if($request -> status_news){
        //         if($request -> status_news == 1 || $request -> status_news == 2){
        //             if($request -> status_news == 1) {
        //                 $model =  $model -> where('save_draft','=',0);

        //             } else if ($request -> status_news == 2) {
        //                 $model =  $model -> where('save_draft',1);
        //                 // dd($model);
        //             }

        //         }  
        //     }
        //     if($request -> news_source){

        //         // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
        //         $model = $model -> whereIn('source', $request -> news_source);
        //     }

        //     if($request -> news_category){
        //         $news_cate_id = $request -> news_category;
        //         $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
        //             $query->whereIn('news_category_id', $news_cate_id);
        //         });
        //     }

        //     if($request -> status_serverity){

        //         $model = $model -> where('serverity', $request -> status_serverity);
        //     }

        // }

        // $model = $model         
        // ->select(DB::raw('count(*) as source_count , source as source'))
        // ->groupBy('source')
        // ->orderBy('source_count', 'desc')
        // ->limit(11)
        // ->get();

        // $host2 = array();
        // foreach($model as $value){
        //     if(empty($value->source)||$value->source=='None'){
        //         if(!isset($host2['None'])){
        //             $host2['None'] = 0;
        //         }
        //         $host2['None'] = $host2['None']+(int)$value->source_count;
        //     }else{
        //         $host2[$value->source] = (int)$value->source_count;
        //     }
        // }
        // arsort($host2);
        // $countLimit = 0;
        // $host = array();
        // foreach ($host2 as $key => $value) {
        //     $countLimit++;
        //     if($countLimit<11){
        //         $host[] = [$key,$value];
        //     }
        // }

        // ----------------------- old -----------------------

        // $host = array();
        // foreach($model as $value){

        //     $host[] = [empty($value->source)?'None':$value->source,(int)$value->source_count];
        // }


        $data_summary = DB::Table('summary')
            ->where([
                'data_key' => 'new',
                'data_key_2' => 'top_10_source',
                'status' => 'Y'
            ])
            ->first();

        $host = @$data_summary->data_value ? json_decode($data_summary->data_value, true) : ' ';

        if ($request->ajax()) {

            return response()->json($host);
        }
    }

    public function load_top_category(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        // ----------------------- old -----------------------

        // $model = New RSSNews();
        // if($request -> search_val == 1){

        //     if($request -> keywords){
        //         $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$request -> keywords.'%')->first();

        //         if($model_where) {
        //             $model = $model -> where('title_en', 'LIKE' ,'%'.$request -> keywords.'%');
        //         } else {
        //             $model = $model -> where('title_th', 'LIKE' ,'%'.$request -> keywords.'%');
        //         }

        //     }

        //     if($request -> isDateSearch == 1){
        //         $date_start = $request->startDate;
        //         $date_end = $request->endDate;

        //         $date_start_explode = explode(" ",$date_start);
        //         $date_start_date = @$date_start_explode[0];
        //         $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
        //         // dd($date_start_time);
        //         $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
        //         // dd($date_start_date_format);
        //         $date_start_time_time = date("H:i", strtotime($date_start_time));
        //         $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
        //         // dd($date_start);

        //         $date_end_explode = explode(" ",$date_end);
        //         $date_end_date = @$date_end_explode[0];
        //         $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
        //         // dd($date_end_time);
        //         $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        //         $date_end_time_time = date("H:i", strtotime($date_end_time));
        //         $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
        //         // dd($date_end_time_time);

        //         // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
        //         $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
        //     }

        //     if($request -> status_news){
        //         if($request -> status_news == 1 || $request -> status_news == 2){
        //             if($request -> status_news == 1) {
        //                 $model =  $model -> where('save_draft','=',0);

        //             } else if ($request -> status_news == 2) {
        //                 $model =  $model -> where('save_draft',1);
        //                 // dd($model);
        //             }

        //         }  
        //     }
        //     if($request -> news_source){

        //         // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
        //         $model = $model -> whereIn('source', $request -> news_source);
        //     }

        //     if($request -> news_category){
        //         $news_cate_id = $request -> news_category;
        //         $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
        //             $query->whereIn('news_category_id', $news_cate_id);
        //         });
        //     }

        //     if($request -> status_serverity){

        //         $model = $model -> where('serverity', $request -> status_serverity);
        //     }

        // }

        // // $RSSNewsCategory = RSSNewsCategory::select('rss_news_id','categories.name')->join('categories','categories.id','=','r_s_s_news_categories.news_category_id')->where('categories.active',1);
        // // dd($RSSNewsCategory->get());
        // // $model = $model         
        // // ->select('categories.name as categories_name_',DB::raw('count(*) as categories_count'))
        // // ->leftjoin('r_s_s_news_categories','r_s_s_news.id','=','r_s_s_news_categories.rss_news_id')
        // // ->leftjoin('categories','categories.id','=','r_s_s_news_categories.news_category_id')
        // // ->where('categories.active',1)
        // // ->where('categories.deleted_at',null)
        // // ->groupBy('categories.name')
        // // ->orderBy('categories_count', 'desc')
        // // ->limit(10)
        // // ->get();

        // $model = $model
        // ->select('name_cat as categories_name_',DB::raw('count(*) as categories_count'))
        // ->leftjoin(DB::raw('(SELECT fx_r_s_s_news_categories.rss_news_id as rssid ,fx_r_s_s_news_categories.news_category_id as category_id, fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
        // where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
        // and fx_categories.active=1 and fx_categories.deleted_at is null) as fx_TotalCatches'), 
        // function($join)
        // {
        //    $join->on('r_s_s_news.id', '=', 'TotalCatches.rssid');
        // })
        // ->addSelect('category_id')
        // ->groupBy('name_cat')
        // ->orderBy('categories_count', 'desc')
        // ->limit(10)
        // ->get();


        // // $sql = "SELECT count(*) as categories_count ,name_cat FROM fx_r_s_s_news LEFT JOIN
        // // (SELECT fx_r_s_s_news_categories.rss_news_id as rssid,fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
        // // where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
        // // and fx_categories.active=1) as test
        // // on fx_r_s_s_news.id = test.rssid
        // // group by name_cat
        // // order by categories_count desc
        // // limit 10" ;
        // // $model = DB::select( DB::raw($sql));

        // //-----------------------------------------------------------------------------

        // $host = array();
        // $color=['#3B3D50','#ECC44D','#DA4C62','#E95C83','#6F57E9','#7698A0','#02CCCD','#A8C5CC','#A0D0C8','#E7DED4'];
        // foreach($model as $key => $value){

        //     $host[] = array(
        //         'name' => empty($value->categories_name_)?'None':$value->categories_name_,
        //         'y' => (int)$value->categories_count,
        //         'color' => $color[$key] ,
        //         'data' => $value->category_id
        //     );


        // }

        // ----------------------- old -----------------------

        $data_summary = DB::Table('summary')
            ->where([
                'data_key' => 'new',
                'data_key_2' => 'top_10_categories',
                'status' => 'Y'
            ])
            ->first();

        $host = @$data_summary->data_value ? json_decode($data_summary->data_value, true) : ' ';

        if ($request->ajax()) {

            return response()->json($host);
        }
    }

    public function tableRssSetting()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $model = RSSData::where('deleted_at', null)->get();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSData $model) {
                return '<label><input type="checkbox" name="rss_id" class="rss_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('link', function (RSSData $model) {
                $html = '';
                $html .= "<a href='" . route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) . "' data-toggle='ajaxModal'>
                    " . $model->link . "
                </a>";
                return $html;
            })
            ->addColumn('transactionRssData_count', function (RSSData $model) {
                $html = '';
                $cursor_count = TransactionRssData::where('rss_id', $model->id)->count();
                if ($cursor_count > 0) {
                    $html .= '<a href="javascript:void(0);" onclick="view_rss_count_data(' . $model->id . ');" >' . $cursor_count . '</a>';
                } else {
                    $html .= '0';
                }
                return $html;
            })
            ->addColumn('status', function (RSSData $model) {
                if ($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="rss-active-' . $model->code . '" onchange="change_rss_active(\'' . $model->code . '\')" ' . $checked_val . ' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function (RSSData $model) {
                $html = '';
                $html .= "<a href='" . route('rssfeedsettings.edit', ['id' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='" . route('rssfeedsettings.delete', ['id' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
            })
            ->rawColumns(['chk', 'link', 'status', 'action', 'transactionRssData_count'])
            ->toJson();
    }




    public function rss_data_create_news($code)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['rss'] = TransactionRssData::where('code', $code)->first();
        $data['RSSNews'] = '';
        if ($data['rss']) {
            // $data['RSSNews'] = RSSNews::where("transaction_rss_id",$data['rss']->id)->first();
        }

        $data['category'] = CategorySettings::where('active', 1)->get();
        return view('rssfeedsettings::modal.create_news')->with($data);
    }

    public function rss_news_edit_news($code)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        $public_date = '';
        $RSSNews = RSSNews::where('code', $code)->first();
        $data['RSSNews'] = $RSSNews;
        if ($RSSNews->public_date) {
            $public_date = $RSSNews->public_date;
        } else {
            $public_date = '';
        }

        // dd('savedraft', $RSSNews->save_draft);

        // dd($data['RSSNews']->source);
        // $data['RSSNews'] = '';
        // if($data['rss']) {
        //     $data['RSSNews'] = RSSNews::where("transaction_rss_id",$data['rss']->id)->first();
        // }

        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);
        $get_source = collect($get_source);

        $data['get_source'] = @$get_source;
        $data['action'] = 'edit';
        $data['category'] = CategorySettings::where('active', 1)->get();
        $data['public_date'] = $public_date;
        $data['savedraft'] = $RSSNews->save_draft;



        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        if (app()->environment('local')) {
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
        } else {
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
        }

        $query_actor = [
            'pulse_id' => $RSSNews->id,
            'mode' => 'news',
            'join' => 'actor',
            'delete_at' => null
        ];
        $options_actor = [];
        $connection_actor = $col_fx_otx_adversaries_related->find($query_actor, $options_actor);
        if ($connection_actor != null) {
            $actors = $connection_actor->toArray();
            $data['actors'] = $actors;
        } else {
            $data['actors'] = null;
        }

        $query_campainge = [
            'pulse_id' => $RSSNews->id,
            'mode' => 'news',
            'join' => 'campainge',
            'delete_at' => null
        ];
        $option_campainge = [];

        $connection_campainge = $col_fx_otx_adversaries_related->find($query_campainge, $option_campainge);
        $campainge = $connection_campainge->toArray();

        if (count($campainge) > 0) {
            foreach ($campainge as $data_campainge) {
                $data['campainge'][] = $data_campainge['adversary_uuid'];
            }
            // $data['campainge'] = $campainge;
        } else {
            $data['campainge'] = null;
        }

        $query_master_campainge = [
            'status' => '1',
            'delete_at' => null
        ];
        $option_master_campainge = [];

        $connection_master_campainge = $col_fx_otx_campaign->find($query_master_campainge, $option_master_campainge);

        if ($connection_master_campainge != null) {
            $master_campainge = $connection_master_campainge->toArray();
            $data['master_campainge'] = $master_campainge;
        } else {
            $data['master_campainge'] = null;
        }
        $data['mode'] = true;
        // dd($data);
        return view('rssfeedsettings::modal.edit_news')->with($data);
    }

    public function rss_news_create_news()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $public_date = '';
        $data['RSSNews'] = array();

        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);

        $get_source = collect($get_source);
        $data['get_source'] = @$get_source;
        $data['action'] = 'create';
        // dd($data['get_source']);
        $data['category'] = CategorySettings::where('active', 1)->get();
        $data['public_date'] = $public_date;


        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        if (app()->environment('local')) {
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
        } else {
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
        }

        $query_master_campainge = [
            'status' => '1'
        ];
        $option_master_campainge = [];

        $connection_master_campainge = $col_fx_otx_campaign->find($query_master_campainge, $option_master_campainge);

        if ($connection_master_campainge != null) {
            $master_campainge = $connection_master_campainge->toArray();
            $data['master_campainge'] = $master_campainge;
        } else {
            $data['master_campainge'] = null;
        }

        // dd($data['master_campainge']);

        return view('rssfeedsettings::modal.edit_news')->with($data);
    }

    public function rss_data_preview_news(Request $request)
    {
        $data['page'] = "Preview News";
        return view('rssfeedsettings::preview_rss_news')->with($data);
    }

    public function rss_data_tags(Request $request)
    {
        $search = $request->searchTerm;
        $get_tags_query = "SELECT * FROM fx_tags ";
        if (!empty($search)) {
            $get_tags_query = $get_tags_query . "WHERE name LIKE '%$search%'";
        }
        $get_tags = DB::select($get_tags_query);
        $get_tags = collect($get_tags);
        return response()->json($get_tags);
    }

    public function rss_data_topics(Request $request)
    {
        $search = $request->searchTerm;
        $get_topic_query = "SELECT * FROM fx_topics ";
        if (!empty($search)) {
            $get_tags_query = $get_topic_query . "WHERE name LIKE '%$search%'";
        }
        $get_topic = DB::select($get_topic_query);
        $get_topic = collect($get_topic);
        return response()->json($get_topic);
    }

    public function rss_data_source(Request $request)
    {
        $search = $request->searchTerm;
        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);
        $get_source = collect($get_source);
        if (!empty($search)) {
            $get_source = $get_source->where('name', 'like', '%' . $search . '%');
        }
        return response()->json($get_source);
    }


    public function rss_data_delete(Request $request, $id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $model = TransactionRssData::where("code", $id)->first();
        $data['rssfeedsettings'] = $model;
        // dd($model);
        return view('rssfeedsettings::modal.rss_data_delete')->with($data);
    }

    public function rss_data_delete_process($id = null)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        // dd($id);
        TransactionRssData::where("code", $id)->delete();


        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_news_delete_change(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        foreach ($request->id_chang as $id) {

            TransactionRssData::where("code", $id)->delete();
        }

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_feed_seting_delete(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }

        foreach ($request->id_chang as $id) {

            RSSData::where("code", $id)->delete();
        }

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => site_url('/rssfeedsettings'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    public function rss_news_delete(Request $request, $id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $model = RSSNews::where("code", $id)->first();
        $data['rssfeedsettings'] = $model;
        // dd($model);
        return view('rssfeedsettings::modal.rss_news_delete')->with($data);
    }

    public function rss_news_delete_process($id = null)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            //  check_permission403();
        }
        $RSS_news = RSSNews::where("code", $id)->first();
        // dd($RSS_news->logo);
        if ($RSS_news->logo != config('app.URL_CENTER_PUBLISH') . '/images/icon/news_default.png') {
            //  unlink($RSS_news->logo);
        }

        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        if (app()->environment('local')) {
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
        } else {
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
        }

        $query_delete = array(
            'pulse_id' => $RSS_news->id,
            'mode' => 'news'
        );
        $option_delete = [];
        $result_delete = $col_fx_otx_adversaries_related->find($query_delete, $option_delete);
        $final_delete = $result_delete->toArray();
        $count_actor = count($final_delete);

        if ($count_actor > 0) {
            $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                $query_delete,
                [
                    '$set' =>
                        [
                            'delete_at' => $date_now
                        ]
                ]
            );
        }

        RSSNews::where("code", $id)->delete();
        // $RSSNews = RSSNews::where('transaction_rss_id',$check_TransactionRssData->id)->first();
        if ($RSS_news) {
            // $RSSNews_del = RSSNews::where('transaction_rss_id',$check_TransactionRssData);
            // $RSSNews_del->delete();
            $RSSNewsCategorycheck = RSSNewsCategory::where('rss_news_id', $RSS_news->id)->get();
            $category_id = [];
            foreach ($RSSNewsCategorycheck as $item) {
                $category_id[] = $item->news_category_id;
            }
            $SiteCategory = SiteCategory::whereIn("category_id", $category_id)->get();
            // dd($SiteCategory[0]->site_email_alert);

            $site_news = [];
            if ($SiteCategory) {
                foreach ($SiteCategory as $SiteCategory_val) {
                    if ($SiteCategory_val) {
                        $site_email_alert = site_config_email_alert::where("site_id", $SiteCategory_val->site_id)->get();
                        if ($site_email_alert) {
                            foreach ($site_email_alert as $site_email_alert_val) {
                                if (!empty($SiteCategory_val->site_email_alert)) {
                                    foreach ($SiteCategory_val->site_email_alert as $emailValue) {
                                        $site_news[] = @$emailValue->site_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $RSSNewsCategory = RSSNewsCategory::where('rss_news_id', $RSS_news->id);
            $RSSNewsCategory->delete();
        }


        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_news_delete_select(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        foreach ($request->id as $id) {

            $RSS_news = RSSNews::where("code", $id)->first();
            if ($RSS_news->logo != config('app.URL_CENTER_PUBLISH') . '/images/icon/news_default.png') {
                unlink($RSS_news->logo);
            }
            RSSNews::where("code", $id)->delete();



            // $RSSNews = RSSNews::where('transaction_rss_id',$check_TransactionRssData->id)->first();
            if ($RSS_news) {
                // $RSSNews_del = RSSNews::where('transaction_rss_id',$check_TransactionRssData);
                // $RSSNews_del->delete();

                $RSSNewsCategory = RSSNewsCategory::where('rss_news_id', $RSS_news->id);
                $RSSNewsCategory->delete();
            }
        }



        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    public function rss_select_actor_news_create(Request $request)
    {

        if ($request->has('q')) {
            $search = $request->q;

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if (app()->environment('local')) {
                $db_name = 'sosecure_threatintelligent_dev';
                $db = $client->$db_name;
                $collection = $db->fx_otx_adversaries;
            } else {
                $db_name = 'sosecure_threatintelligent_dev_test';
                $db = $client->$db_name;
                $collection = $db->fx_otx_adversaries;
            }

            $query = [

                'name' => new \MongoDB\BSON\Regex($search),
                'delete_at' => null

            ];

            $option = [];

            $final = $collection->find($query, $option);
            $result = $final->toArray();
        }

        return response()->json($result);
    }
    //-------------------------------------------------------------------------------
    public function rss_data_store_news_create(Request $request)
    {

        // dd($request->all());
        // Log::info($request->all());
        // return response()->json([
        //     'message' => 'Your isPublished is : ' . $request->is_published,    
        // ]
        // );

        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            //   check_permission403();
        }

        $input = $request->all();

        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);

            $image = $request->file('logo');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/logo_news');
            $image->move($destinationPath, $imagename);
            // $logo = asset('images/logo_news/'.$imagename);
            $logo = '/images/logo_news/' . $imagename;
        } else {
            $logo = '/images/icon/news_default.png';
        }


        $SiteCategory = SiteCategory::whereIn("category_id", $request->category_news)->where('active', 1)->get();

        $email_site_a = [];
        $site_news = [];
        if ($SiteCategory) {
            foreach ($SiteCategory as $SiteCategory_val) {
                if ($SiteCategory_val) {
                    if (!empty($SiteCategory_val->site_email_alert)) {
                        foreach ($SiteCategory_val->site_email_alert as $valueEmail) {
                            $email_site_a[] = @$valueEmail->email;
                            $site_news[] = @$valueEmail->site_id;
                        }
                    }
                    // if(@$SiteCategory_val->site_email_alert->email) {
                    //     $email_site_alert[] = @$SiteCategory_val->site_email_alert->email;
                    // }
                }
            }
            // $email_site_a = ['oatnunkung@gmail.com','oatnunkung88@gmail.com'];
            $site_news = array_unique($site_news);
            $email_site_alert = array_unique($email_site_a);

            // Log::info($email_site_alert);
            // $email_site_alert_implode = implode(",",$email_site_alert);
        }

        /*      return ajaxResponse(
                  [
                      'cate' => $SiteCategory,
                      'test' => $email_site_alert,

                      'message'  => langapp('changes_saved_successful'),
                      'redirect' => route('rssfeedsettings.news'),
                  ],
                  true,
                  Response::HTTP_INTERNAL_SERVER_ERROR
              );
      */
        // $site_config_email_alert = site_config_email_alert::where()

        $RSSNews_check = RSSNews::where("code", $request->rss_code)->first();
        $fail_mail = [];
        $output_mail = [];
        if (@$RSSNews_check) {



            $RSSNews_check->code = generator_uuid();
            $RSSNews_check->logo = config('app.URL_CENTER_PUBLISH') . $logo;
            $RSSNews_check->title_th = $request->title_th;
            $RSSNews_check->title_en = $request->title_en;
            $RSSNews_check->serverity = $request->serverity;
            $RSSNews_check->source = $request->source;
            $RSSNews_check->public_date = Carbon::parse($request->public_date);
            // $RSSNews_check->published = $request->is_published;
            $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput

            if ($detail_th) {
                $dom = new \domdocument();
                if ($dom->getelementsbytagname('img')) {
                    $dom->loadHtml(
                        '<?xml encoding="UTF-8">' . $detail_th,
                        LIBXML_HTML_NOIMPLIED |
                        LIBXML_HTML_NODEFDTD |
                        LIBXML_NOERROR |
                        LIBXML_NOWARNING
                    );
                    //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                    $images = $dom->getelementsbytagname('img');
                    //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                    foreach ($images as $k => $img) {
                        $data = $img->getattribute('src');

                        //Link url
                        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                        if (preg_match($reg_exUrl, $data, $url_image)) {
                            $url = $url_image[0];
                            $contextOptions = [
                                "ssl" => [
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true,
                                ]
                            ];
                            $context = stream_context_create($contextOptions);

                            // ดึงภาพ
                            $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                            if ($image !== false) {
                                // สร้าง data URI แบบ base64
                                $data = 'data:image/jpg;base64,' . base64_encode($image);
                            }
                        }

                        //base64
                        $img_check_src = explode(";", $data);
                        if (@$img_check_src[1]) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);
                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                        } else {
                        }
                    }
                    $detail_th = $dom->savehtml();
                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews_check->detail_th = $detail_th;

            $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
            if ($detail_en) {
                $dom = new \domdocument();
                if ($dom->getelementsbytagname('img')) {
                    $dom->loadHtml(
                        '<?xml encoding="UTF-8">' . $detail_en,
                        LIBXML_HTML_NOIMPLIED |
                        LIBXML_HTML_NODEFDTD |
                        LIBXML_NOERROR |
                        LIBXML_NOWARNING
                    );
                    //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                    $images = $dom->getelementsbytagname('img');
                    //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                    foreach ($images as $k => $img) {
                        $data = $img->getattribute('src');

                        //Link url
                        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                        if (preg_match($reg_exUrl, $data, $url_image)) {
                            $url = $url_image[0];

                            // เพิ่ม context เพื่อข้าม SSL verification
                            $contextOptions = [
                                "ssl" => [
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true,
                                ]
                            ];
                            $context = stream_context_create($contextOptions);

                            // ดึงภาพ
                            $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                            if ($image !== false) {
                                // สร้าง data URI แบบ base64
                                $data = 'data:image/jpg;base64,' . base64_encode($image);
                            }
                        }

                        //base64
                        $img_check_src = explode(";", $data);
                        if (@$img_check_src[1]) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);
                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                        } else {
                        }
                    }
                    $detail_en = $dom->savehtml();
                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews_check->detail_en = $detail_en;

            $RSSNews_check->status = $request->status ? 1 : 0;
            if ($request->formsubmit == 'formDraft') {
                $RSSNews_check->save_draft = 1;
            } else {
                $RSSNews_check->save_draft = 0;
            }
            $RSSNews_check->save();

            $RSSNewsCategory_del = RSSNewsCategory::where("rss_news_id", $RSSNews_check->id);
            $RSSNewsCategory_del->delete();

            if (!empty($request->category_news)) {
                foreach ($request->category_news as $item) {
                    //เช็คก่อน หมวดหมู่บันทึกซ้ำกัน
                    $RSSNewsCategory_check_count = RSSNewsCategory::where("rss_news_id", $RSSNews_check->id)->where('news_category_id', $item)->count();
                    if ($RSSNewsCategory_check_count == 0) {
                        $RSSNewsCategory = new RSSNewsCategory();
                        $RSSNewsCategory->code = generator_uuid();
                        $RSSNewsCategory->rss_news_id = $RSSNews_check->id;
                        $RSSNewsCategory->news_category_id = $item;
                        $RSSNewsCategory->status = 1;
                        $RSSNewsCategory->save();

                    }

                }
            }

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);

            if (app()->environment('local')) {
                $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                $collection_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
            } else {
                $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                $collection_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
            }

            $query_delete = array(
                'pulse_id' => $RSSNews_check->id,
                'mode' => 'news',
                'join' => 'actor'
            );
            $option_delete = [];
            $result_delete = $col_fx_otx_adversaries_related->find($query_delete, $option_delete);
            $final_delete = $result_delete->toArray();
            $count_actor = count($final_delete);

            if ($count_actor > 0) {
                $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                    $query_delete,
                    [
                        '$set' =>
                            [
                                'delete_at' => $date_now
                            ]
                    ]
                );
            }

            if (!empty(@$request->actor)) {
                $checkSuccess = true;


                // $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                //     ['adversary_uuid' => $uuid],
                //     ['$set' => 
                //         [
                //             'delete_at' => $date_now
                //         ]
                //     ]
                // );

                foreach ($request->actor as $data_actor) {
                    $query = [
                        'adversary_uuid' => $data_actor
                    ];
                    $option = [];

                    $result = $col_fx_otx_adversaries->find($query, $option);

                    $data_result = array();

                    foreach ($result as $data) {
                        $data_result['id'] = $data->_id;
                        $data_result['uuid'] = $data->adversary_uuid;
                        $data_result['name'] = $data->name;
                    }

                    $data_adv_related = array(
                        'adversary_uuid' => $data_result['uuid'],
                        'adversary_name' => $data_result['name'],
                        'pulse_id' => $RSSNews_check->id,
                        'pulse_name' => '',
                        'title_th' => $RSSNews_check->detail_th,
                        'title_en' => $RSSNews_check->detail_en,
                        'mode' => 'news',
                        'join' => 'actor',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );

                    $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->insertOne($data_adv_related);
                }
            }

            $query_delete_camp = array(
                'pulse_id' => $RSSNews_check->id,
                'mode' => 'news',
                'join' => 'campainge'
            );
            $option_delete_camp = [];
            $result_delete_ = $col_fx_otx_adversaries_related->find($query_delete_camp, $option_delete_camp);
            $final_delete_camp = $result_delete_->toArray();
            $count_campainge = count($final_delete_camp);

            if ($count_campainge > 0) {
                $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                    $query_delete_camp,
                    [
                        '$set' =>
                            [
                                'delete_at' => $date_now
                            ]
                    ]
                );
            }

            if (!empty($request->new_campainge)) {
                $checkSuccess = true;
                $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);


                foreach ($request->new_campainge as $data_campainge) {
                    $query = [
                        'campainge_uuid' => $data_campainge
                    ];
                    $option = [];

                    $result = $collection_campaign->find($query, $option);

                    $data_result = array();

                    foreach ($result as $data) {
                        $data_result['id'] = $data->_id;
                        $data_result['uuid'] = $data->campainge_uuid;
                        $data_result['name'] = $data->name;
                    }

                    $data_adv_related = array(
                        'adversary_uuid' => $data_result['uuid'],
                        'adversary_name' => $data_result['name'],
                        'pulse_id' => $RSSNews_check->id,
                        'pulse_name' => '',
                        'title_th' => $RSSNews_check->detail_th,
                        'title_en' => $RSSNews_check->detail_en,
                        'mode' => 'news',
                        'join' => 'campainge',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );

                    $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->insertOne($data_adv_related);
                }
            }

            // if(!empty($request -> tags)){
            //     foreach($request -> tags as $item){
            //         $tags = Tags::where('name', $item)->first();
            //         if($tags){
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         }else{
            //             $tags = new Tags;
            //             $tags -> name = $item;
            //             $tags -> save();

            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         } 
            //     }
            // }

            // if(!empty($request -> topic)){
            //     foreach($request -> topic as $item){
            //         $Topic = Topic::where('name', $item)->first();
            //         if($Topic){
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         }else{
            //             $Topic = new Topic();
            //             $Topic -> name = $item;
            //             $Topic -> status = 1;
            //             $Topic -> save();

            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         } 
            //     }
            // }
            //  $email_site_alert = ['yongyot.kamma@gmail.com', 'yongyot.kha@mtsc.co.th'];

            if ($request->formsubmit !== 'formDraft') {
                if ($request->sent_mail == 1) {
                    if (!empty($email_site_alert)) {
                        $news = [
                            'news' => $RSSNews_check,
                        ];

                        // ส่งอีเมลแบบกลุ่ม
                        Mail::to($email_site_alert)->send(new NewsMail($news));

                        // ตรวจสอบผลลัพธ์
                        if (count(Mail::failures()) === 0) {
                            foreach ($email_site_alert as $email) {
                                LogEmail::create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'News'
                                ]);
                            }
                        } else {
                            foreach ($email_site_alert as $email) {
                                $status = in_array($email, Mail::failures()) ? 'Fail' : 'Success';
                                LogEmail::create([
                                    'to' => $email,
                                    'status' => $status,
                                    'subject' => 'News'
                                ]);
                            }
                        }
                    }

                }
                // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                // $mail = ['yongyot.kamma@gmail.com'];
                $mail = $email_site_alert;
                /*   // dd($mail);
                   foreach ($mail as $data) {
                       // dd($data);
                       // $output_mail[] = $data;
                       $this->news = [
                           'news' => $RSSNews_check,
                       ];
                       Mail::to($data)->send(new NewsMail($this->news));
                       if (Mail::failures()) {
                           // return response showing failed emails
                           $fail_mail[] = $data;
                       }
                   }
                   */
            }
        } else {

            // dd($RSSNews_check -> id);
            // dd($input);
            //check ซ้ำ ถ้ามีชื่อข่าวกับวันที public ซ้ำกัน ไม่ให้สร้างได้ในวันนั้น
            $today = Carbon::today();
            $RSSNews_check = RSSNews::where("title_th", $request->title_th)
                ->where("title_en", $request->title_en)
                ->whereDate("created_at", $today)
                ->first();

            if ($RSSNews_check) {
                // ห้ามสร้างซ้ำ

                return ajaxResponse(
                    array_filter([
                        'mail' => $output_mail ?? [],
                        'fail_mail' => $fail_mail ?? [],
                        'message' => 'มีข่าวชื่อเดียวกันสร้างไว้แล้วในวันนี้',
                        'redirect' => route('rssfeedsettings.news'),
                        'warning' => 'มีข่าวชื่อเดียวกันสร้างไว้แล้วในวันนี้',
                    ]),
                    false,
                    200
                );
            }

            $RSSNews = new RSSNews();
            $RSSNews->code = generator_uuid();
            $RSSNews->logo = config('app.URL_CENTER_PUBLISH') . $logo;
            $RSSNews->title_th = $request->title_th;
            $RSSNews->title_en = $request->title_en;
            $RSSNews->serverity = $request->serverity;
            $RSSNews->source = $request->source;
            $RSSNews->public_date = Carbon::parse($request->public_date);
            // $RSSNews->published = $request->is_published;
            $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
            if ($detail_th) {
                $dom = new \domdocument();
                if ($dom->getelementsbytagname('img')) {
                    $dom->loadHtml(
                        '<?xml encoding="UTF-8">' . $detail_th,
                        LIBXML_HTML_NOIMPLIED |
                        LIBXML_HTML_NODEFDTD |
                        LIBXML_NOERROR |
                        LIBXML_NOWARNING
                    );
                    //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                    $images = $dom->getelementsbytagname('img');
                    //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                    foreach ($images as $k => $img) {
                        $data = $img->getattribute('src');
                        //Link url
                        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                        if (preg_match($reg_exUrl, $data, $url_image)) {
                            $url = $url_image[0];
                            // เพิ่ม context เพื่อข้าม SSL verification
                            $contextOptions = [
                                "ssl" => [
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true,
                                ]
                            ];
                            $context = stream_context_create($contextOptions);

                            // ดึงภาพ
                            $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                            if ($image !== false) {
                                // สร้าง data URI แบบ base64
                                $data = 'data:image/jpg;base64,' . base64_encode($image);
                            }
                        }

                        //base64
                        $img_check_src = explode(";", $data);
                        if (@$img_check_src[1]) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);
                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                        } else {
                        }
                    }
                    $detail_th = $dom->savehtml();
                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews->detail_th = $detail_th;

            $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
            if ($detail_en) {
                $dom = new \domdocument();
                if ($dom->getelementsbytagname('img')) {
                    $dom->loadHtml(
                        '<?xml encoding="UTF-8">' . $detail_en,
                        LIBXML_HTML_NOIMPLIED |
                        LIBXML_HTML_NODEFDTD |
                        LIBXML_NOERROR |
                        LIBXML_NOWARNING
                    );
                    //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                    $images = $dom->getelementsbytagname('img');
                    //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                    foreach ($images as $k => $img) {
                        $data = $img->getattribute('src');
                        //Link url
                        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                        if (preg_match($reg_exUrl, $data, $url_image)) {
                            $url = $url_image[0];
                            // เพิ่ม context เพื่อข้าม SSL verification
                            $contextOptions = [
                                "ssl" => [
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true,
                                ]
                            ];
                            $context = stream_context_create($contextOptions);

                            // ดึงภาพ
                            $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                            if ($image !== false) {
                                // สร้าง data URI แบบ base64
                                $data = 'data:image/jpg;base64,' . base64_encode($image);
                            }
                        }

                        //base64
                        $img_check_src = explode(";", $data);
                        if (@$img_check_src[1]) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);
                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                        } else {
                        }
                    }
                    $detail_en = $dom->savehtml();
                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews->detail_en = $detail_en;

            $RSSNews->status = $request->status ? 1 : 0;
            if ($request->formsubmit == 'formDraft') {
                $RSSNews->save_draft = 1;
            }
            $RSSNews->save();
            if (!empty($request->category_news)) {
                foreach ($request->category_news as $item) {
                    $RSSNewsCategory_check_count = RSSNewsCategory::where("rss_news_id", $RSSNews->id)->where('news_category_id', $item)->count();
                    if ($RSSNewsCategory_check_count == 0) {
                        $RSSNewsCategory = new RSSNewsCategory();
                        $RSSNewsCategory->code = generator_uuid();
                        $RSSNewsCategory->rss_news_id = $RSSNews->id;
                        $RSSNewsCategory->news_category_id = $item;
                        $RSSNewsCategory->status = 1;
                        $RSSNewsCategory->save();
                    }
                }
            }

            $new_id = $RSSNews->id;
            $new_code = $RSSNews->code;
            $new_title_th = $request->title_th;
            $new_title_en = $request->title_en;
            $new_detail_th = $request->detail_th;
            $new_detail_en = $request->detail_en;

            //------------------------- actor ------------------------------
            if (!empty(@$request->actor)) {
                $checkSuccess = true;
                $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

                $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                if (app()->environment('local')) {
                    $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                    $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                } else {
                    $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                    $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                }

                // $query_delete = array(
                //     'pulse_id' => $new_id,
                //     'mode' => 'news',
                //     'join' => 'actor'
                // );
                // $option_delete = [];
                // $result_delete = $col_fx_otx_adversaries_related->find($query_delete,$option_delete);
                // $final_delete = $result_delete->toArray();
                // $count_actor = count($final_delete);

                // if($count_actor > 0)
                // {
                //     $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                //         $query_delete,
                //         ['$set' => 
                //             [
                //                 'delete_at' => $date_now
                //             ]
                //         ]
                //     );
                // }

                // $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                //     ['adversary_uuid' => $uuid],
                //     ['$set' => 
                //         [
                //             'delete_at' => $date_now
                //         ]
                //     ]
                // );

                foreach ($request->actor as $data_actor) {
                    $query = [
                        'adversary_uuid' => $data_actor
                    ];
                    $option = [];

                    $result = $col_fx_otx_adversaries->find($query, $option);

                    $data_result = array();

                    foreach ($result as $data) {
                        $data_result['id'] = $data->_id;
                        $data_result['uuid'] = $data->adversary_uuid;
                        $data_result['name'] = $data->name;
                    }

                    $data_adv_related = array(
                        'adversary_uuid' => $data_result['uuid'],
                        'adversary_name' => $data_result['name'],
                        'pulse_id' => $new_id,
                        'pulse_name' => '',
                        'title_th' => $new_title_th,
                        'title_en' => $new_title_en,
                        'mode' => 'news',
                        'join' => 'actor',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );

                    $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->insertOne($data_adv_related);
                }

                // $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->updateOne(
                //     ['adversary_uuid' => $uuid,'pulse_id' => $value["id"]],
                //     ['$set' => [
                //         'updated_at' => $date_now ,
                //         'updated_by' => "system",
                //         'adversary_name' => $adversary_name,
                //         'pulse_name' => isset($value["name"])?$value["name"]:"",
                //     ],
                //         '$setOnInsert' => [
                //         'created_at' => $date_now ,
                //         'created_by' => "system",
                //         ],
                //     ],
                //     ['upsert' => true]
                // );
            }

            if (!empty($request->new_campainge)) {
                $checkSuccess = true;
                $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

                $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                if (app()->environment('local')) {
                    $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                    $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                    $collection_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
                } else {
                    $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                    $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                    $collection_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
                }

                foreach ($request->new_campainge as $data_campainge) {
                    $query = [
                        'campainge_uuid' => $data_campainge
                    ];
                    $option = [];

                    $result = $collection_campaign->find($query, $option);

                    $data_result = array();

                    foreach ($result as $data) {
                        $data_result['id'] = $data->_id;
                        $data_result['uuid'] = $data->campainge_uuid;
                        $data_result['name'] = $data->name;
                    }

                    $data_adv_related = array(
                        'adversary_uuid' => $data_campainge,
                        'adversary_name' => $data_result['name'],
                        'pulse_id' => $new_id,
                        'pulse_name' => '',
                        'title_th' => $new_title_th,
                        'title_en' => $new_title_en,
                        'mode' => 'news',
                        'join' => 'campainge',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );

                    $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->insertOne($data_adv_related);
                }
            }


            // if(!empty($request -> tags)){
            //     foreach($request -> tags as $item){
            //         $tags = Tags::where('name', $item)->first();
            //         if($tags){
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         }else{
            //             $tags = new Tags;
            //             $tags -> name = $item;
            //             $tags -> save();

            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         } 
            //     }
            // }

            // if(!empty($request -> topic)){
            //     foreach($request -> topic as $item){
            //         $Topic = Topic::where('name', $item)->first();
            //         if($Topic){
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         }else{
            //             $Topic = new Topic();
            //             $Topic -> name = $item;
            //             $Topic -> status = 1;
            //             $Topic -> save();

            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         } 
            //     }
            // }
            //  $email_site_alert = ['yongyot.kamma@gmail.com', 'yongyot.kha@mtsc.co.th'];
            if ($request->formsubmit !== 'formDraft') {
                if ($request->sent_mail == 1) {
                    if (!empty($email_site_alert)) {
                        $news = [
                            'news' => $RSSNews,
                        ];

                        // ส่งอีเมลแบบกลุ่ม
                        Mail::to($email_site_alert)->send(new NewsMail($news));

                        // ตรวจสอบผลลัพธ์
                        if (count(Mail::failures()) === 0) {
                            foreach ($email_site_alert as $email) {
                                LogEmail::create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'News'
                                ]);
                            }
                        } else {
                            foreach ($email_site_alert as $email) {
                                $status = in_array($email, Mail::failures()) ? 'Fail' : 'Success';
                                LogEmail::create([
                                    'to' => $email,
                                    'status' => $status,
                                    'subject' => 'News'
                                ]);
                            }
                        }
                    }

                }
                // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                //  $mail = ['yongyot.kamma@gmail.com'];
                $mail = $email_site_alert;
                /*    // dd($mail);
                    foreach ($mail as $data) {
                        // dd($data);
                        // $output_mail[] = $data;
                        $this->news = [
                            'news' => $RSSNews,
                        ];
                        Mail::to($data)->send(new NewsMail($this->news));
                        if (Mail::failures()) {
                            // return response showing failed emails
                            $fail_mail[] = $data;
                        }
                    }
                    */
            }
        }


        // ------------ top 10 source
        // -------------------------------------------------------------------------------------------------

        $data_graph_source = RSSNews::select(
            DB::raw('count(*) as source_count, source as source')
        )
            ->groupBy('source')
            ->orderBy('source_count', 'desc')
            ->limit(11)
            ->get();

        $data_arr_source = array();
        foreach ($data_graph_source as $value) {
            if (empty($value->source) || $value->source == 'None') {
                if (!isset($data_arr_source['None'])) {
                    $data_arr_source['None'] = 0;
                }

                $data_arr_source['None'] = $data_arr_source['None'] + (int) $value->source_count;
            } else {
                $data_arr_source[$value->source] = (int) $value->source_count;
            }
        }

        arsort($data_arr_source);
        $countLimit = 0;
        $main_arr_source = array();
        foreach ($data_arr_source as $key => $value) {
            $countLimit++;
            if ($countLimit < 11) {
                $main_arr_source[] = [$key, $value];
            }
        }

        $encoded_data_graph_source = json_encode($main_arr_source);

        $main_data_source = [
            'site' => '',
            'data_key' => 'new',
            'data_key_2' => 'top_10_source',
            'data_value' => $encoded_data_graph_source
        ];

        $check_data_source = DB::Table('summary')
            ->where([
                'data_key' => 'new',
                'data_key_2' => 'top_10_source',
                'status' => 'Y'
            ])
            ->first();

        if ($check_data_source) {
            DB::Table('summary')
                ->where([
                    'data_key' => 'new',
                    'data_key_2' => 'top_10_source',
                    'status' => 'Y'
                ])
                ->update($main_data_source);
        } else {
            DB::table('summary')->insert($main_data_source);
        }

        // ------------ top 10 category
        // -------------------------------------------------------------------------------------------------

        $data_graph_category = RSSNews::select(
            'name_cat as categories_name_',
            DB::raw('count(*) as categories_count')
        )
            ->leftjoin(
                DB::raw('(
                    SELECT 
                        fx_r_s_s_news_categories.rss_news_id as rssid ,
                        fx_r_s_s_news_categories.news_category_id as category_id, 
                        fx_categories.name as name_cat 
                    FROM 
                        fx_r_s_s_news_categories,fx_categories
                    where 
                        fx_r_s_s_news_categories.news_category_id = fx_categories.id and 
                        fx_categories.active=1 and 
                        fx_categories.deleted_at is null
                ) as fx_TotalCatches'),
                function ($join) {
                    $join->on('r_s_s_news.id', '=', 'TotalCatches.rssid');
                }
            )
            ->addSelect('category_id')
            ->groupBy('name_cat')
            ->orderBy('categories_count', 'desc')
            ->limit(10)
            ->get();

        $main_arr_category = array();
        $color = ['#3B3D50', '#ECC44D', '#DA4C62', '#E95C83', '#6F57E9', '#7698A0', '#02CCCD', '#A8C5CC', '#A0D0C8', '#E7DED4'];
        foreach ($data_graph_category as $key => $value) {
            $main_arr_category[] = array(
                'name' => empty($value->categories_name_) ? 'None' : $value->categories_name_,
                'y' => (int) $value->categories_count,
                'color' => $color[$key],
                'data' => $value->category_id
            );
        }

        $encoded_data_graph_category = json_encode($main_arr_category);

        $main_data_category = [
            'site' => '',
            'data_key' => 'new',
            'data_key_2' => 'top_10_categories',
            'data_value' => $encoded_data_graph_category
        ];

        $check_data_category = DB::Table('summary')
            ->where([
                'data_key' => 'new',
                'data_key_2' => 'top_10_categories',
                'status' => 'Y'
            ])
            ->first();

        if ($check_data_category) {
            DB::Table('summary')
                ->where([
                    'data_key' => 'new',
                    'data_key_2' => 'top_10_categories',
                    'status' => 'Y'
                ])
                ->update($main_data_category);
        } else {
            DB::table('summary')->insert($main_data_category);
        }


        return ajaxResponse(
            [
                // 'cate' => $SiteCategory,
                // 'test' => $email_site_alert,
                'mail' => $output_mail,
                'fail_mail' => $fail_mail,
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.news'),
                'warning' => ''
            ],
            true,
            Response::HTTP_OK
        );
    }
    //-----------------------------------------------------
    public function rss_data_store_news(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        // dd($request);
        // return $_POST['detail_th'];
        // return '4444 '.$request -> detail_th;
        $SiteCategory = SiteCategory::whereIn("category_id", $request->category_news)->where('active', 1)->get();
        // dd($SiteCategory[0]->site_email_alert);
        $site_news = [];
        $email_site_a = [];
        if ($SiteCategory) {
            foreach ($SiteCategory as $SiteCategory_val) {
                if ($SiteCategory_val) {
                    if (!empty($SiteCategory_val->site_email_alert)) {
                        foreach ($SiteCategory_val->site_email_alert as $emailValue) {
                            $email_site_a[] = @$emailValue->email;
                            $site_news[] = @$emailValue->site_id;
                        }
                    }
                }
            }
            // $email_site_alert_implode = implode(",",$email_site_alert);
            // $email_site_a = ['oatnunkung@gmail.com','oatnunkung88@gmail.com'];
            $email_site_alert = array_unique($email_site_a);

        }

        if ($request->formsubmit == 'formSavingAndRun') {
            return ajaxResponse(
                [
                    'message' => "Successfully",
                    'redirect' => route('rssfeedsettings.rss_data'),
                ],
                true,
                Response::HTTP_OK
            );
        } else {
            $TransactionRssData = TransactionRssData::where('code', $request->rss_code)->first();
            // $logo = asset('images/image-not-found.jpg');
            $logo = '/images/image-not-found.jpg';

            if ($TransactionRssData) {
                if ($TransactionRssData->enclosure) {
                    $logo = $TransactionRssData->enclosure;
                }
                $RSSNews_check = RSSNews::where("transaction_rss_id", $TransactionRssData->id)->first();
                if ($RSSNews_check) {
                    $RSSNews_check->code = generator_uuid();
                    $RSSNews_check->logo_rss = config('app.URL_CENTER_PUBLISH') . $logo;
                    $RSSNews_check->title_th = $request->title_th;
                    $RSSNews_check->title_en = $request->title_en;
                    $RSSNews_check->source = $request->source;
                    $RSSNews_check->link = $TransactionRssData->link;
                    $RSSNews_check->public_date = Carbon::parse($request->public_date);
                    $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
                    // dd($detail_th);
                    if ($detail_th) {
                        $dom = new \domdocument();
                        if ($dom->getelementsbytagname('img')) {
                            $dom->loadHtml(
                                '<?xml encoding="UTF-8">' . $detail_th,
                                LIBXML_HTML_NOIMPLIED |
                                LIBXML_HTML_NODEFDTD |
                                LIBXML_NOERROR |
                                LIBXML_NOWARNING
                            );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach ($images as $k => $img) {
                                $data = $img->getattribute('src');
                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if (preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    // เพิ่ม context เพื่อข้าม SSL verification
                                    $contextOptions = [
                                        "ssl" => [
                                            "verify_peer" => false,
                                            "verify_peer_name" => false,
                                            "allow_self_signed" => true,
                                        ]
                                    ];
                                    $context = stream_context_create($contextOptions);

                                    // ดึงภาพ
                                    $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                                    if ($image !== false) {
                                        // สร้าง data URI แบบ base64
                                        $data = 'data:image/jpg;base64,' . base64_encode($image);
                                    }
                                }

                                //base64
                                $img_check_src = explode(";", $data);
                                if (@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data) = explode(',', $data);
                                    $data = base64_decode($data);
                                    //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name = time() . $k . '.png';
                                    //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') . '/' . $image_name;
                                    //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                                } else {
                                }
                            }
                            $detail_th = $dom->savehtml();
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }


                    $RSSNews_check->detail_th = $detail_th;

                    $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
                    if ($detail_en) {
                        $dom = new \domdocument();
                        if ($dom->getelementsbytagname('img')) {
                            $dom->loadHtml(
                                '<?xml encoding="UTF-8">' . $detail_en,
                                LIBXML_HTML_NOIMPLIED |
                                LIBXML_HTML_NODEFDTD |
                                LIBXML_NOERROR |
                                LIBXML_NOWARNING
                            );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach ($images as $k => $img) {
                                $data = $img->getattribute('src');
                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if (preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    // เพิ่ม context เพื่อข้าม SSL verification
                                    $contextOptions = [
                                        "ssl" => [
                                            "verify_peer" => false,
                                            "verify_peer_name" => false,
                                            "allow_self_signed" => true,
                                        ]
                                    ];
                                    $context = stream_context_create($contextOptions);

                                    // ดึงภาพ
                                    $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                                    if ($image !== false) {
                                        // สร้าง data URI แบบ base64
                                        $data = 'data:image/jpg;base64,' . base64_encode($image);
                                    }
                                }

                                //base64
                                $img_check_src = explode(";", $data);
                                if (@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data) = explode(',', $data);
                                    $data = base64_decode($data);
                                    //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name = time() . $k . '.png';
                                    //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') . '/' . $image_name;
                                    //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                                } else {
                                }
                            }
                            $detail_en = $dom->savehtml();
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews_check->detail_en = $detail_en;

                    $RSSNews_check->transaction_rss_id = $TransactionRssData->id;
                    $RSSNews_check->status = $request->status ? 1 : 0;
                    if ($request->formsubmit == 'formDraft') {
                        $RSSNews_check->save_draft = 1;
                    } else {
                        $RSSNews_check->save_draft = 0;
                    }
                    $RSSNews_check->save();


                    $RSSNewsCategory_del = RSSNewsCategory::where("rss_news_id", $RSSNews_check->id);
                    $RSSNewsCategory_del->delete();

                    if (!empty($request->category_news)) {
                        foreach ($request->category_news as $item) {
                            $RSSNewsCategory = new RSSNewsCategory();
                            $RSSNewsCategory->code = generator_uuid();
                            $RSSNewsCategory->rss_news_id = $RSSNews_check->id;
                            $RSSNewsCategory->news_category_id = $item;
                            $RSSNewsCategory->status = 1;
                            $RSSNewsCategory->save();
                        }
                    }


                    //$email_site_alert = ['yongyot.kamma@gmail.com', 'yongyot.kha@mtsc.co.th'];

                    if ($request->formsubmit !== 'formDraft') {
                        if ($request->sent_mail == 1) {
                            if ($email_site_alert) {
                                foreach ($email_site_alert as $data) {
                                    $news = [
                                        'news' => $RSSNews_check,
                                    ];
                                    // dd($this->news);
                                    $sent = Mail::to($data)->send(new NewsMail($news));
                                    if ($sent) {
                                        LogEmail::Create([
                                            'to' => $data,
                                            'status' => 'Success',
                                            'subject' => 'News'
                                        ]);
                                    } else {
                                        LogEmail::Create([
                                            'to' => $data,
                                            'status' => 'Fail',
                                            'subject' => 'News'
                                        ]);
                                    }
                                }
                            }
                        }
                        // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                        // $mail = ['todeooooo@gmail.com', 'yongyot.kamma@gmail.com'];
                        //   $mail = ['yongyot.kamma@gmail.com'];
                        /*     $mail = $email_site_alert;
                            // dd($mail);
                            foreach ($mail as $data) {
                                // dd($data);
                                $this->news = [
                                    'news' => $RSSNews_check,
                                ];
                                Mail::to($data)->send(new NewsMail($this->news));
                            }
                            */
                        foreach ($site_news as $data) {
                            $TransactionClientNews = TransactionClientNews::where('site_id', $data)->where('transaction_id', $RSSNews_check->id)->first();
                            if ($TransactionClientNews) {
                                $TransactionClientNews->transaction_mode = 'update';
                                $TransactionClientNews->transaction_data_status = 1;
                                $TransactionClientNews->status = 1;
                                $TransactionClientNews->save();
                            } else {
                                $TransactionClientNews = new TransactionClientNews();
                                $TransactionClientNews->site_id = $data;
                                $TransactionClientNews->transaction_id = $RSSNews_check->id;
                                $TransactionClientNews->transaction_mode = 'update';
                                $TransactionClientNews->transaction_data_status = 1;
                                $TransactionClientNews->status = 1;
                                $TransactionClientNews->save();
                            }
                            if ($SiteCategory) {
                                foreach ($SiteCategory as $SiteCategories) {
                                    $fx_transaction_client_news_categories = fx_transaction_client_news_categories::where('site_id', $data)->where('transaction_id', $SiteCategories->category_id)->first();
                                    if ($fx_transaction_client_news_categories) {
                                        $fx_transaction_client_news_categories->transaction_mode = 'update';
                                        $fx_transaction_client_news_categories->transaction_data_status = 1;
                                        $fx_transaction_client_news_categories->status = 1;
                                        $fx_transaction_client_news_categories->save();
                                    } else {
                                        $fx_transaction_client_news_categories = new fx_transaction_client_news_categories();
                                        $fx_transaction_client_news_categories->site_id = $data;
                                        $fx_transaction_client_news_categories->transaction_id = $SiteCategories->category_id;
                                        $fx_transaction_client_news_categories->transaction_mode = 'update';
                                        $fx_transaction_client_news_categories->transaction_data_status = 1;
                                        $fx_transaction_client_news_categories->status = 1;
                                        $fx_transaction_client_news_categories->save();
                                    }
                                }
                            }
                        }
                    }
                } else {

                    $RSSNews = new RSSNews();
                    $RSSNews->code = generator_uuid();
                    $RSSNews->logo_rss = config('app.URL_CENTER_PUBLISH') . $logo;
                    $RSSNews->title_th = $request->title_th;
                    $RSSNews->title_en = $request->title_en;
                    $RSSNews->source = $request->source;
                    $RSSNews->link = $TransactionRssData->link;
                    $RSSNews->public_date = Carbon::parse($request->public_date);
                    $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
                    if ($detail_th) {
                        $dom = new \domdocument();
                        if ($dom->getelementsbytagname('img')) {
                            $dom->loadHtml(
                                '<?xml encoding="UTF-8">' . $detail_th,
                                LIBXML_HTML_NOIMPLIED |
                                LIBXML_HTML_NODEFDTD |
                                LIBXML_NOERROR |
                                LIBXML_NOWARNING
                            );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach ($images as $k => $img) {
                                $data = $img->getattribute('src');
                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if (preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    // เพิ่ม context เพื่อข้าม SSL verification
                                    $contextOptions = [
                                        "ssl" => [
                                            "verify_peer" => false,
                                            "verify_peer_name" => false,
                                            "allow_self_signed" => true,
                                        ]
                                    ];
                                    $context = stream_context_create($contextOptions);

                                    // ดึงภาพ
                                    $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                                    if ($image !== false) {
                                        // สร้าง data URI แบบ base64
                                        $data = 'data:image/jpg;base64,' . base64_encode($image);
                                    }
                                }

                                //base64
                                $img_check_src = explode(";", $data);
                                if (@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data) = explode(',', $data);
                                    $data = base64_decode($data);
                                    //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name = time() . $k . '.png';
                                    //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') . '/' . $image_name;
                                    //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                                } else {
                                }
                            }
                            $detail_th = $dom->savehtml();
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews->detail_th = $detail_th;

                    $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
                    if ($detail_en) {
                        $dom = new \domdocument();
                        if ($dom->getelementsbytagname('img')) {
                            $dom->loadHtml(
                                '<?xml encoding="UTF-8">' . $detail_en,
                                LIBXML_HTML_NOIMPLIED |
                                LIBXML_HTML_NODEFDTD |
                                LIBXML_NOERROR |
                                LIBXML_NOWARNING
                            );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach ($images as $k => $img) {
                                $data = $img->getattribute('src');
                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if (preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    // เพิ่ม context เพื่อข้าม SSL verification
                                    $contextOptions = [
                                        "ssl" => [
                                            "verify_peer" => false,
                                            "verify_peer_name" => false,
                                            "allow_self_signed" => true,
                                        ]
                                    ];
                                    $context = stream_context_create($contextOptions);

                                    // ดึงภาพ
                                    $image = @file_get_contents($url, false, $context); // ใส่ @ เพื่อ suppress warning

                                    if ($image !== false) {
                                        // สร้าง data URI แบบ base64
                                        $data = 'data:image/jpg;base64,' . base64_encode($image);
                                    }
                                }

                                //base64
                                $img_check_src = explode(";", $data);
                                if (@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data) = explode(',', $data);
                                    $data = base64_decode($data);
                                    //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name = time() . $k . '.png';
                                    //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') . '/' . $image_name;
                                    //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH') . '/images/file_editor/' . $image_name);
                                } else {
                                }
                            }
                            $detail_en = $dom->savehtml();
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews->detail_en = $detail_en;

                    $RSSNews->transaction_rss_id = $TransactionRssData->id;
                    $RSSNews->status = $request->status ? 1 : 0;
                    if ($request->formsubmit == 'formDraft') {
                        $RSSNews->save_draft = 1;
                    }
                    $RSSNews->save();

                    if (!empty($request->category_news)) {
                        foreach ($request->category_news as $item) {
                            $RSSNewsCategory = new RSSNewsCategory();
                            $RSSNewsCategory->code = generator_uuid();
                            $RSSNewsCategory->rss_news_id = $RSSNews->id;
                            $RSSNewsCategory->news_category_id = $item;
                            $RSSNewsCategory->status = 1;
                            $RSSNewsCategory->save();
                        }
                    }

                    if (!empty($request->tags)) {
                        // foreach($request -> tags as $item){
                        //     $tags = Tags::where('name', $item)->first();
                        //     if($tags){
                        //         $NewsTag = new NewsTag();
                        //         $NewsTag -> code = generator_uuid();
                        //         $NewsTag -> tag_id = $tags -> id;
                        //         $NewsTag -> rss_news_id = $RSSNews -> id;
                        //         $NewsTag -> status = 1;
                        //         $NewsTag -> save();
                        //     }else{
                        //         $tags = new Tags;
                        //         $tags -> name = $item;
                        //         $tags -> save();

                        //         $NewsTag = new NewsTag();
                        //         $NewsTag -> code = generator_uuid();
                        //         $NewsTag -> tag_id = $tags -> id;
                        //         $NewsTag -> rss_news_id = $RSSNews -> id;
                        //         $NewsTag -> status = 1;
                        //         $NewsTag -> save();
                        //     } 
                        // }
                    }

                    if (!empty($request->topic)) {
                        // foreach($request -> topic as $item){
                        //     $Topic = Topic::where('name', $item)->first();
                        //     if($Topic){
                        //         $NewsTopics = new NewsTopics();
                        //         $NewsTopics -> code = generator_uuid();
                        //         $NewsTopics -> topic_id = $Topic -> id;
                        //         $NewsTopics -> rss_news_id = $RSSNews -> id;
                        //         $NewsTopics -> status = 1;
                        //         $NewsTopics -> save();
                        //     }else{
                        //         $Topic = new Topic();
                        //         $Topic -> name = $item;
                        //         $Topic -> status = 1;
                        //         $Topic -> save();

                        //         $NewsTopics = new NewsTopics();
                        //         $NewsTopics -> code = generator_uuid();
                        //         $NewsTopics -> topic_id = $Topic -> id;
                        //         $NewsTopics -> rss_news_id = $RSSNews -> id;
                        //         $NewsTopics -> status = 1;
                        //         $NewsTopics -> save();
                        //     } 
                        // }
                    }
                    //$email_site_alert = ['yongyot.kamma@gmail.com', 'yongyot.kha@mtsc.co.th'];
                    if ($request->formsubmit !== 'formDraft') {
                        if ($request->sent_mail == 1) {
                            if ($email_site_alert) {
                                foreach ($email_site_alert as $data) {
                                    $news = [
                                        'news' => $RSSNews,
                                    ];
                                    // dd($this->news);
                                    $sent = Mail::to($data)->send(new NewsMail($news));
                                    if ($sent) {
                                        LogEmail::Create([
                                            'to' => $data,
                                            'status' => 'Success',
                                            'subject' => 'News'
                                        ]);
                                    } else {
                                        LogEmail::Create([
                                            'to' => $data,
                                            'status' => 'Fail',
                                            'subject' => 'News'
                                        ]);
                                    }
                                }
                            }
                        }
                        // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                        // $mail = ['yongyot.kamma@gmail.com'];
                        // dd($mail);
                        /*
                        $mail = $email_site_alert;
                        foreach ($mail as $data) {
                            // dd($data);
                            $this->news = [
                                'news' => $RSSNews,
                            ];
                            Mail::to($data)->send(new NewsMail($this->news));
                        }
                        */
                        foreach ($site_news as $data) {
                            $TransactionClientNews = TransactionClientNews::where('site_id', $data)->where('transaction_id', $RSSNews->id)->first();
                            if ($TransactionClientNews) {
                                $TransactionClientNews->transaction_mode = 'insert';
                                $TransactionClientNews->transaction_data_status = 1;
                                $TransactionClientNews->status = 1;
                                $TransactionClientNews->save();
                            } else {
                                $TransactionClientNews = new TransactionClientNews();
                                $TransactionClientNews->site_id = $data;
                                $TransactionClientNews->transaction_id = $RSSNews->id;
                                $TransactionClientNews->transaction_mode = 'insert';
                                $TransactionClientNews->transaction_data_status = 1;
                                $TransactionClientNews->status = 1;
                                $TransactionClientNews->save();
                            }
                            if ($SiteCategory) {
                                foreach ($SiteCategory as $SiteCategories) {
                                    $fx_transaction_client_news_categories = fx_transaction_client_news_categories::where('site_id', $data)->where('transaction_id', $SiteCategories->category_id)->first();
                                    if ($fx_transaction_client_news_categories) {
                                        $fx_transaction_client_news_categories->transaction_mode = 'insert';
                                        $fx_transaction_client_news_categories->transaction_data_status = 1;
                                        $fx_transaction_client_news_categories->status = 1;
                                        $fx_transaction_client_news_categories->save();
                                    } else {
                                        $fx_transaction_client_news_categories = new fx_transaction_client_news_categories();
                                        $fx_transaction_client_news_categories->site_id = $data;
                                        $fx_transaction_client_news_categories->transaction_id = $SiteCategories->category_id;
                                        $fx_transaction_client_news_categories->transaction_mode = 'insert';
                                        $fx_transaction_client_news_categories->transaction_data_status = 1;
                                        $fx_transaction_client_news_categories->status = 1;
                                        $fx_transaction_client_news_categories->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return ajaxResponse(
                [
                    // 'test' => $_POST['detail_th'],
                    'message' => "Successfully",
                    'redirect' => route('rssfeedsettings.news'),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function rss_setting()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_setting')->with($data);
    }

    public function rss_logs()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_logs')->with($data);
    }

    public function rss_news()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        // $data['page'] = langapp('rss_logs');
        $data['page'] = langapp('news');
        // $data['Category'] = CategorySettings::where('active',1)->get();
        $data['category'] = CategorySettings::where('active', 1)->get();
        return view('rssfeedsettings::rss_news')->with($data);
    }

    public function rss_feed_all()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_feed_all')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }


        $RSSData = new RSSData;
        $RSSData->code = generator_uuid();
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->status_rss ? 1 : 0;
        $RSSData->created_by = @Auth::user()->id;
        $RSSData->save();

        $settings = SiteSettings::select('id')->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', '>=', date("Y-m-d H:i:s"))->where('active', 1)->where('deleted_at', null)->get();
        foreach ($settings as $setting) {
            $transaction_client_rss = transaction_client_rss::where('site_id', $setting->id)->where('transaction_id', $RSSData->id)->first();
            if ($transaction_client_rss) {
                $transaction_client_rss->transaction_mode = 'insert';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            } else {
                $transaction_client_rss = new transaction_client_rss();
                $transaction_client_rss->site_id = $setting->id;
                $transaction_client_rss->transaction_id = $RSSData->id;
                $transaction_client_rss->transaction_mode = 'insert';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            }
        }



        return ajaxResponse(
            [
                'id' => $RSSData->id,
                'message' => langapp('saved_successfully'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        return view('rssfeedsettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $get_data = RSSData::where("code", $id)->first();

        $data['rssfeedsettings'] = $get_data;
        // $data['page'] = $this->getPage();
        return view('rssfeedsettings::modal.update')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CreateRssRequest $request, $id = null)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        //  dd($request);
        //  exit();
        $RSSData = RSSData::where("code", $id)->first();
        // $CategorySettings->update($request->all());
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->active ? 1 : 0;
        $RSSData->save();

        $settings = SiteSettings::select('id')->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', '>=', date("Y-m-d H:i:s"))->where('active', 1)->where('deleted_at', null)->get();
        foreach ($settings as $setting) {
            $transaction_client_rss = transaction_client_rss::where('site_id', $setting->id)->where('transaction_id', $RSSData->id)->first();
            if ($transaction_client_rss) {
                $transaction_client_rss->transaction_mode = 'update';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            } else {
                $transaction_client_rss = new transaction_client_rss();
                $transaction_client_rss->site_id = $setting->id;
                $transaction_client_rss->transaction_id = $RSSData->id;
                $transaction_client_rss->transaction_mode = 'update';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            }
        }

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id' => $RSSData->id,
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
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

    public function change_status(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        // dd($request);
        // exit();
        $rss_code = $this->request->code;
        // $data['category_id'] = $this->request->category_id;
        // $CategorySettings = $this->categorySettings->findOrFail($data['category_id']);
        $rss = RSSData::where("code", $rss_code)->first();
        // $CategorySettings->update($request->all());
        // $CategorySettings->name = $request->name;
        $rss->status = $rss->status == 1 ? 0 : 1;
        $rss->save();

        $settings = SiteSettings::select('id')->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', '>=', date("Y-m-d H:i:s"))->where('active', 1)->where('deleted_at', null)->get();
        foreach ($settings as $setting) {
            $transaction_client_rss = transaction_client_rss::where('site_id', $setting->id)->where('transaction_id', $rss->id)->first();
            if ($transaction_client_rss) {
                $transaction_client_rss->transaction_mode = 'update';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            } else {
                $transaction_client_rss = new transaction_client_rss();
                $transaction_client_rss->site_id = $setting->id;
                $transaction_client_rss->transaction_id = $rss->id;
                $transaction_client_rss->transaction_mode = 'update';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            }
        }

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id' => $rss->id,
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_status_news(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $RSSNews = RSSNews::where('code', $request->code)->first();
        $RSSNews->status = $request->active;
        $RSSNews->save();
        return ajaxResponse(
            [
                'id' => $RSSNews->id,
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete(Request $id)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $data['rssfeedsettings'] = $id;
        return view('rssfeedsettings::modal.delete')->with($data);
    }



    public function delete_process($id = null)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['news']) {
            check_permission403();
        }
        $model = RSSData::where("code", $id)->first();
        // dd($model);
        $settings = SiteSettings::select('id')->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', '>=', date("Y-m-d H:i:s"))->where('active', 1)->where('deleted_at', null)->get();
        foreach ($settings as $setting) {
            $transaction_client_rss = transaction_client_rss::where('site_id', $setting->id)->where('transaction_id', $model->id)->first();
            if ($transaction_client_rss) {
                $transaction_client_rss->transaction_mode = 'delete';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            } else {
                $transaction_client_rss = new transaction_client_rss();
                $transaction_client_rss->site_id = $setting->id;
                $transaction_client_rss->transaction_id = $model->id;
                $transaction_client_rss->transaction_mode = 'delete';
                $transaction_client_rss->transaction_data_status = 1;
                $transaction_client_rss->status = 1;
                $transaction_client_rss->save();
            }
        }
        $model->delete();
        return ajaxResponse(
            [
                'message' => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function new_select_campainge(Request $request)
    {
        $input = $request->all();

        if ($request->has('q')) {
            $search = $request->q;

            $DB_MONGO_KEY = env("DB_MONGO_DEV");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            if (app()->environment('local')) {
                $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                $collection_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
            } else {
                $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                $collection_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
            }

            $query_search = [
                'name' => new \MongoDB\BSON\Regex($search),
                'delete_at' => null
            ];

            $option_search = [];

            $final_search = $collection_campaign->find($query_search, $option_search);
            $result_search = $final_search->toArray();
        }

        return response()->json($result_search);
    }
}
