<?php

namespace Modules\Certificate\Http\Controllers;

use App\Entities\fx_transaction_client_news_categories;
use App\LogEmail;
use App\Mail\NewsMail;
use App\siteNewsRelated;
use App\Topic;
use App\transaction_client_rss;
use App\TransactionClientNews;
use App\FXCategories;
use Modules\Certificate\Http\Requests\CreateRssRequest;
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
use Illuminate\Support\Facades\Mail;
use Modules\Certificate\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\Certificate\Entities\NewsTag;
use Modules\Certificate\Entities\NewsTopics;
use Modules\Certificate\Entities\RSSData;
use Modules\Certificate\Entities\RSSNews;
use Modules\Certificate\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\Certificate\Entities\TransactionRssData;
use Modules\SiteSettings\Entities\SiteSettings;
use Yajra\DataTables\DataTables;


class CertificateController extends Controller
{

    protected $item;
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }

    public function index()
    {
        // 
        return view('certificate::index');
    }

    public function create()
    {
        // return view('rssfeedsettings::create');
    }

}
