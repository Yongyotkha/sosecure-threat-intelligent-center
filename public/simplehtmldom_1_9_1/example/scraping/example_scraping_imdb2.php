<?php
include_once('../../simple_html_dom.php');


// $url = 'https://www.blognone.com/node'; // page to scrape https://digg.com
$maxAttempts = 5; // number of attempts before failure
$timeout = 30; // 30 seconds
$sleep = rand(3,8); // 3 seconds

$page = 1;
$url_page = 'https://www.infosecurity-magazine.com';
$url_page_arg = '/news/page-';
$load_status = 'all';//daily_day,all
$working = 1;//1,0

   ////-test
// $html = $doc->load('https://digg.com/');
// echo $html;
// exit();

if($working == 1) {//เช็คสถานะเปิดการทำงาน
  // $scrapedData = getScrapedData($url_page, $maxAttempts, $timeout, $sleep);
    $scrapedData = load_more_page($load_status,$page_load,$url_page,$url_page_arg,$maxAttempts,$timeout,$sleep);

  if ($scrapedData !== false) {
    // creating a csv file
    // $csv = fopen('data.csv', 'w');

    // populating the csv file with the scraped data
    // foreach ($scrapedData as $fields) {
    //   fputcsv($csv, $fields);
    // }

    // fclose($csv);
    // $data_ready = pull_content($scrapedData);

    // $show_content = show_content($data_ready);//
    //----start------------------------------//
 
      // $page_load = 1;
      // load_more_page($load_status,$page_load,$url_page,$url_page_arg,$maxAttempts,$timeout,$sleep);

    //----end------------------------------//

    // echo "Script successfully completed!\n";
  } else {
    // echo "Script failed!\n";
  }
}



function getScrapedData($url_page, $maxAttempts = 3, $timeout = 60, $sleep = 5) {

  $attempt = 1;

  while ($attempt <= $maxAttempts) {

    $curl = curl_init();

    $headers = array(
      'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'accept-language: th,en;q=0.9',
      'cookie: __cfduid=dcbd4fe1d846f2854801b9b7c8179da831615067764; has_js=1',
      'Cache-Control: no-cache',
      'sec-fetch-dest: document',
      'sec-fetch-mode: navigate',
      'sec-fetch-site: none',
      'sec-fetch-user: ?1'
  );

    // setting a randomly chosen User-Agent
    curl_setopt($curl, CURLOPT_USERAGENT, getRandomUserAgent());
    curl_setopt($curl, CURLOPT_COOKIE, '__cfduid=dcbd4fe1d846f2854801b9b7c8179da831615067764; has_js=1');
    curl_setopt($curl, CURLOPT_URL, $url_page);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // configuring TOR proxy
    // curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1");
    // curl_setopt($curl, CURLOPT_PROXYPORT, "9050");
    // curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

    // setting a timeout
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

    // certification bundle downloaded here: https://curl.haxx.se/docs/caextract.html
    curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $html = curl_exec($curl);

    // on failure
    if ($html == false) {
      // printing error message
      echo curl_error($curl) . "\n";

      $attempt += 1;

      // waiting $sleep seconds on failure before a new attempt
        sleep($sleep);
    } else {
        // $ret = $html->find('a');
        // echo $html;
        // $html = $doc->load('https://digg.com/');
        
//     $doc = new HtmlWeb();
// $html = $doc->load('https://digg.com/');
        $html = str_get_html($html);
                    // $doc = new HtmlWeb();
                    // $html = $doc->load('https://digg.com/');
      // $html = $doc->load('https://slashdot.org/');
                    // $html = $doc->load('<html><body>Hello!</body></html>');
                    // echo $html;
                    // exit();
        // sleep($sleep);
    return $html;
        }
  }

  return false;
}


    
    function pull_content($html,$p=0) {//insert-data-to-array
            //--start---ส่วน edit--------------------------------------------------------------//
            $data= [];
            foreach($html->find('div[class="webpage-item with-thumbnail"]') as $article) {
                // Find the title of the current article
                if($title = $article->find('h3[itemprop="name"]',0)) {
                    $item['title'] = trim($title->plaintext);
                    
                } else {
                    $item['title'] = 'Unknown title';
                }
                if($shot_content = $article->find('p[itemprop="articleBody"]',0)) {
                    $item['shot_content'] = trim($shot_content->plaintext);
                } else {
                    $item['shot_content'] = '';
                }
                if($time = $article->find('time[itemprop="datePublished"]',0)) {
                    $item['time'] = trim($time->plaintext);
                } else {
                    $item['time'] = '';
                }

                $data[] = $item;
                // $item = 11;
            }
            $html->clear();
            unset($html);
            return $data;
    }

    function show_content($data_show) {//loop-show
            // Display your own page to the user
            if($data_show) {
              foreach($data_show as $item) {
                  echo 
                  '<time datetime="" itemprop="datePublished">'.$item['time'].'</time>
                  <h2>'.$item['title'].'</h2>
                  <p>'.$item['shot_content'].'</p>
                  <ul>
                  <!--<li>'.@$item['details'].'</li>
                  <li>'.@$item['diggs'].'</li>-->
                  </ul>';
              }
            }
    }

    function load_more_page($load_status,$page_load,$url_page,$url_page_arg,$maxAttempts,$timeout,$sleep) {
      for ($page = 1; $page < 6; $page++) {
        $url = $url_page.$url_page_arg.$page;
        // echo $url;
        // exit();
        $scrapedData = getScrapedData($url, $maxAttempts, $timeout, $sleep);
        // var_dump($scrapedData);
        // exit();
          if ($scrapedData !== false) {
            // echo "Script successfully completed!\n";
            $data_ready = pull_content($scrapedData,$page);
            $show_content = show_content($data_ready);
          } else {
            // echo "Script failed!\n";
          }
        
      }
    }


function getRandomUserAgent() {//rand user-agent
  // default User-Agent
  $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0";

  // reading a randomly chosen User-Agent string from the User-Agent list file
  if ($file = fopen("user-agents_chrome_browser_89-0.txt", "r")) {
    $userAgents = array();

    while (!feof($file)) {
      $userAgents[] = fgets($file);
    }

    $userAgent = $userAgents[array_rand($userAgents)];
  }

  return trim($userAgent);
}


?>