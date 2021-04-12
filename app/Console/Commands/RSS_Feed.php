<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\RSSFeedSettings\Entities\RSS;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use App\Entities\TransactionBatchjob;
class RSS_Feed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:RSS_Feed';

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

        $TransactionBatchjob_Update = TransactionBatchjob::where('mode','batchjob_rssfeed')->first();
        if ($TransactionBatchjob_Update->progress == 1) {
            $TransactionBatchjob_Update->progress = 2;
            $TransactionBatchjob_Update->transcation_date_start =date("Y-m-d H:i:s");
            $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
            $TransactionBatchjob_Update->save();
            $RSSList = RSS::where('status', '1')->get();
            foreach ($RSSList as $key => $value) {
                echo $value->url;
                $this->output_rss_feed($value->id, $value->url, 20, true, true, 200);
                RSS::where('id',$value->id)->update(array(
                    'feed_last'=>date("Y-m-d H:i:s"),
                ));

            }

            $TransactionBatchjob_Update = TransactionBatchjob::where('mode','batchjob_rssfeed')->first();
            $TransactionBatchjob_Update->progress = 1;
            $TransactionBatchjob_Update->transcation_date_end =date("Y-m-d H:i:s");
            $TransactionBatchjob_Update->save();

            $date_delele = date("Y-m-d 00:00:00");
            TransactionRssData::where('transcation_datetime', '<=', $date_delele)->delete();
        }


        $this->info('Update check completed');
    }
    public function GUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public function get_rss_feed_as_html($rss_id, $feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0, $cache_timeout = 7200, $cache_prefix = "")
    {

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

            if (!TransactionRssData::where('link', $link)->first()) {
                $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
                $TransactionRssData_save = new TransactionRssData;
                $TransactionRssData_save->code = $this->GUID();
                $TransactionRssData_save->transaction_id = 2;
                $TransactionRssData_save->rss_id = $rss_id;
                $TransactionRssData_save->status = 1;
                $TransactionRssData_save->title = $title;
                $TransactionRssData_save->link = $link;

                $TransactionRssData_save->link = $link;

                //  echo $title;
                $result .= '<li class="feed-item">';
                $result .= '<div class="feed-title"><strong><a href="' . $link . '" title="' . $title . '">' . $title . '</a></strong></div>';
                if ($show_date) {
                    $date = date('Y-m-d', strtotime($feed[$x]['date']));
                    $TransactionRssData_save->pubDate = $date;
                    $TransactionRssData_save->transcation_date = $date;

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
                $TransactionRssData_save->description = $description;
                $TransactionRssData_save->enclosure = $enclosure;
                $TransactionRssData_save->isPermaLink = $link;
                $TransactionRssData_save->transcation_datetime = date("Y-m-d H:i:s");

                $TransactionRssData_save->save();
            }

        }
        $result .= '</ul>';

        return $result;
    }

    public function output_rss_feed($rss_id, $feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0)
    {
        try {
            $this->get_rss_feed_as_html($rss_id, $feed_url, $max_item_cnt, $show_date, $show_description, $max_words);
        } catch (Exception $e) {
            $error["Exception"] = $e;
        }
    }

}
