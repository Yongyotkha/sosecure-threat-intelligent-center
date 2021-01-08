<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\RSSFeedSettings\Entities\RSS;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;

class RSS_Feed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDRSS_Feed';

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


        $RSSList = RSS::where('status', '1')->get();
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        foreach ($RSSList as $key => $value) {

            $this->output_rss_feed($value->id, $value->url, $value->name, 20, true, true, 200,$date_now);

        }

        // $FeedReader_data =   FeedReader::read('https://www.darkreading.com/rss_simple.asp');
        // foreach ($FeedReader_data as $key => $value) {
        //     print_r($value);
        //  }

        //  $dateStamp = date("Y/m/d h:i:s");
        // echo $dateStamp;

        $this->info('Update check completed');
    }
    public function GUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public function get_rss_feed_as_html($get_InsertedId,$date_now,$rss_id, $feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0, $cache_timeout = 7200, $cache_prefix = "")
    {

        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_rss_data = $clientMD->sosecure_threatintelligent->fx_transaction_rss_data;
        $cache_prefix = base_path() . "\\app\\Console\\Commands\\temp\\rss2html-";
        $result = "";
        // get feeds and parse items
        $rss = new \DOMDocument();
        $cache_file = $cache_prefix . md5($feed_url);
        // load from file or load content
        if ($cache_timeout > 0 &&
            is_file($cache_file) &&
            (filemtime($cache_file) + $cache_timeout > time())) {
            $rss->load($cache_file);
        } else {
            $rss->load($feed_url);
            if ($cache_timeout > 0) {
                $rss->save($cache_file);
            }
        }
        $feed = array();
        foreach ($rss->getElementsByTagName('item') as $node) {
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'content' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
            );
            $content = $node->getElementsByTagName('encoded'); // <content:encoded>
            if ($content->length > 0) {
                $item['content'] = $content->item(0)->nodeValue;
            }
            array_push($feed, $item);
        }
        // real good count
        if ($max_item_cnt > count($feed)) {
            $max_item_cnt = count($feed);
        }
        $result .= '<ul class="feed-lists">';
        for ($x = 0; $x < $max_item_cnt; $x++) {
            $link = $feed[$x]['link'];
            $findUnique = $col_fx_transaction_rss_data->findOne(['link' => $link]);
            if (empty($findUnique)) {
                $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
                $TransactionRssData_save_code = $this->GUID();

                $TransactionRssData_save_transaction_id = $get_InsertedId;

                $TransactionRssData_save_rss_id = $rss_id;
                $TransactionRssData_save_status = 1;
                $TransactionRssData_save_title = $title;
                $TransactionRssData_save_link = $link;


                //  echo $title;
                $result .= '<li class="feed-item">';
                $result .= '<div class="feed-title"><strong><a href="' . $link . '" title="' . $title . '">' . $title . '</a></strong></div>';
                if ($show_date) {
                    $date = date('Y-m-d', strtotime($feed[$x]['date']));
                    $TransactionRssData_save_pubDate = $date;
                    $TransactionRssData_save_transcation_date = new UTCDateTime(strtotime($date)*1000);

                    //$result .= '<small class="feed-date"><em>Posted on '.$date.'</em></small>';
                }
                $description = "";
                $enclosure = "";
                if ($show_description) {
                    $description = $feed[$x]['desc'];
                    $content = $feed[$x]['content'];
                    // find the img
                    $has_image = preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);
                    // no html tags
                    $description = strip_tags(preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', "$1$3", $description), '');
                    // whether cut by number of words
                    if ($max_words > 0) {
                        $arr = explode(' ', $description);
                        if ($max_words < count($arr)) {
                            $description = '';
                            $w_cnt = 0;
                            foreach ($arr as $w) {
                                $description .= $w . ' ';
                                $w_cnt = $w_cnt + 1;
                                if ($w_cnt == $max_words) {
                                    break;
                                }
                            }
                            $description .= " ...";
                        }
                    }
                    // add img if it exists
                    if ($has_image == 1) {
                        // $description = '<img class="feed-item-image" src="' . $image['src'] . '" />' . $description;
                        $enclosure = $image['src'];
                    }
                    //  $result .= '<div class="feed-description">' . $description;
                    //  $result .= ' <a href="'.$link.'" title="'.$title.'">Continue Reading &raquo;</a>'.'</div>';
                }
                $TransactionRssData_save_description = $description;
                $TransactionRssData_save_enclosure = $enclosure;
                $TransactionRssData_save_isPermaLink = $link;
                $TransactionRssData_save_transcation_datetime = $date_now;

                $insertOneResult = $col_fx_transaction_rss_data->insertOne([
                    'code' => $TransactionRssData_save_code,
                    'transaction_id' => $TransactionRssData_save_transaction_id,
                    'rss_id' =>  $TransactionRssData_save_rss_id,
                    'status' => $TransactionRssData_save_status,
                    'title' => $TransactionRssData_save_title,
                    'link' => $TransactionRssData_save_link,
                    'pubDate' => $TransactionRssData_save_pubDate,
                    'transcation_date' => $TransactionRssData_save_transcation_date,
                    'description' => $TransactionRssData_save_description,
                    'enclosure' => $TransactionRssData_save_enclosure,
                    'isPermaLink' => $TransactionRssData_save_isPermaLink,
                    'transcation_datetime' => $TransactionRssData_save_transcation_datetime,
                    'created_at' => $date_now,
                    'created_by' => "system",
                    'deleted_at' => null,
                    'updated_at' => $date_now,
                    'updated_by' => "system",

                ]);
            }

        }
        $result .= '</ul>';

        return $result;
    }

    public function output_rss_feed($rss_id, $feed_url, $rss_name, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0,$date_now)
    {
        try {
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $collectionStamp = $clientMD->sosecure_threatintelligent->fx_transaction_rss_stamp;
            $insertOneResult = $collectionStamp->insertOne([
                'code' => generator_uuid(),
                'transaction_id' => $rss_id,
                'transaction_name' => $feed_url,
                'transaction_url' => $rss_name,
                'transaction_date' => date("Y-m-d"),
                'status' => 1,
                'created_at' => $date_now,
                'created_by' => "system",
                'updated_at' => $date_now,
                'updated_by' => "system",
                'deleted_at' => null,
            ]);
            $get_InsertedId = $insertOneResult->getInsertedId();
            $this->get_rss_feed_as_html($get_InsertedId,$date_now,$rss_id, $feed_url, $max_item_cnt, $show_date, $show_description, $max_words);
            $updateResult2 = $collectionStamp->updateOne(
                ['_id' => $get_InsertedId],
                ['$set' => ['status' => 2]]
            );

        } catch (Exception $e) {
            $error["Exception"] = $e;
        }
    }

}
