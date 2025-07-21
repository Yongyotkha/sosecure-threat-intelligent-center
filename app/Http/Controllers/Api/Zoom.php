<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//echo "<script type='text/javascript'>"; 
//echo "alert($schedule_d $schedule_s $schedule_e);"; 
//echo "</script>"; 

class Zoom {

    public function create_user($email,$fname,$sname){
        $ch = curl_init("https://api.zoom.us/v2/users");
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6IkNjVTd6V0R3US0tRDBDclF0eFE5LUEiLCJleHAiOjE2MTE2MzE4OTcsImlhdCI6MTYxMTYyNjQ5NH0.9LZ2TXV2BX1kKBralnEsn2b5WwX7YDYJMrq_U3RTCjk";

        $payload = json_encode( 
            array( 
                "action"        => "custCreate",
                "user_info"     => array(
                    "email"     => $email,
                    "type"      => 1,
                    "first_name"=> $fname,
                    "last_name" => $sname
                )
            ));

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_success = substr($httpcode,0,1)==2;
        curl_close ($ch);
        
        if($is_success){
            $res =  (array) json_decode($response);
            $return_data = array("status"=>1,"id"=>$res['id']);
        }else{
            $return_data = array("status"=>0);
        }
        return $return_data;
    }
    public function update_user($zoom_id,$fname,$sname){
        $ch = curl_init("https://api.zoom.us/v2/users/".$zoom_id);
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6IkNjVTd6V0R3US0tRDBDclF0eFE5LUEiLCJleHAiOjE2MTE2MzE4OTcsImlhdCI6MTYxMTYyNjQ5NH0.9LZ2TXV2BX1kKBralnEsn2b5WwX7YDYJMrq_U3RTCjk";

        $payload = json_encode( 
            array("first_name"=> $fname,
                "last_name" => $sname
            ));

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_success = substr($httpcode,0,1)==2;
        curl_close ($ch);
        if($is_success){
            $return_data = array("status"=>1);
        }else{
            $return_data = array("status"=>0);
        }
        return $return_data;
    }
    public function create_meeting($zoom_id,$topic,$description,$start_time,$end_time){
        $start_time     = date('Y-m-d H:i:s', strtotime($start_time." - 15 minute"));
        $end_time       = date('Y-m-d H:i:s', strtotime($end_time." + 15 minute"));
        $duration       = (strtotime($end_time) - strtotime($start_time))/60;
     
       
        $ch = curl_init("https://api.zoom.us/v2/users/".$zoom_id."/meetings");
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6IkNjVTd6V0R3US0tRDBDclF0eFE5LUEiLCJleHAiOjE2MTE2MzE4OTcsImlhdCI6MTYxMTYyNjQ5NH0.9LZ2TXV2BX1kKBralnEsn2b5WwX7YDYJMrq_U3RTCjk";

        $payload = json_encode(
            array(
                "topic"=> $topic,
                "type"=> 2,
                "start_time"=> $start_time,
                "duration"=> $duration,
                "timezone"=> "Asia/Bangkok",
                "agenda"=> $description,
                "settings"=> array(
                    "host_video"        => true,
                    "participant_video" => false,
                    "join_before_host"  => true,
                    "enforce_login"     => false
                )));

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_success = substr($httpcode,0,1)==2;
        curl_close ($ch);
        if($is_success){

            $res =  (array) json_decode($response);
            $return_data = array("status"=>1,"meeting_id"=>$res['uuid'],"host_url"=>$res['start_url'],"join_url"=>$res['join_url']);
        }else{
            $return_data = array("status"=>0);
        }
        return $return_data;
echo "alert(schedule_date);"; 
    }
    public function update_meeting($meeting_id,$topic,$description,$start_time,$end_time){
        $ch = curl_init("https://api.zoom.us/v2/meetings/".$meeting_id);
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6IkNjVTd6V0R3US0tRDBDclF0eFE5LUEiLCJleHAiOjE2MTE2MzE4OTcsImlhdCI6MTYxMTYyNjQ5NH0.9LZ2TXV2BX1kKBralnEsn2b5WwX7YDYJMrq_U3RTCjk";

        if($topic!='')      $data['topic']       = $topic;
        if($description!='')$data['description'] = $description;
        if($start_time!='') $data['start_time']  = $start_time;
        if($end_time!='')   $data['end_time']    = $topiend_timec;

        $payload = json_encode($data);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_success = substr($httpcode,0,1)==2;
        curl_close ($ch);
        if($is_success){
            $return_data = array("status"=>1);
        }else{
            $return_data = array("status"=>0);
        }
        return $return_data;
    }
    public function test(){
        echo count(explode(" ", "Admin f"))-1?explode(" ", "Admin f")[1]:'noooo';
    }
    public function get_report(){
        $ch = curl_init("https://api.zoom.us/v2/report/daily");
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6IkNjVTd6V0R3US0tRDBDclF0eFE5LUEiLCJleHAiOjE2MTE2MzE4OTcsImlhdCI6MTYxMTYyNjQ5NH0.9LZ2TXV2BX1kKBralnEsn2b5WwX7YDYJMrq_U3RTCjk";

        $data['year']   =2019;
        $data['month']   =8;
        $payload = json_encode($data);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_success = substr($httpcode,0,1)==2;
        curl_close ($ch);

        return (array) json_decode($response);;
    }
}