 <body>

      <form action="https://insight.sosecure.co.th/Darkweb/search.php" method="post">
        Search: <input type="text" name="term" /><br />
        Payload: <input type="text" name="payload" value="q"  /><br />
        Time Stamp From: <input type="text" name="time_stamp_from" value="2020-12-07T10:01:24Z" /><br />
        Time Stamp To: <input type="text" name="time_stamp_to"  value="2021-04-07T10:01:24Z" /><br />
      <input type="submit" name="submit" value="Submit" />
      </form>

</body>

<?php
 $term =  $_REQUEST['term'];
 $payload =  $_REQUEST['payload']; 
 $time_stamp_from =  $_REQUEST['time_stamp_from']; 
 $time_stamp_to =  $_REQUEST['time_stamp_to']; 

 if($term){
 $t = '/usr/bin/php /var/www/html/insight.sosecure.co.th/threat-intelligent-center/artisan app:MDFeedDarkWeb_Token '.$term.' '. $payload.' '. $time_stamp_from.' '. $time_stamp_to;
 $output = shell_exec($t);
 //echo "<pre>$output</pre>";
 print_r($t);
 }
 //print_r($output);
?>