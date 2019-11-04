<?php

require "twitteroauth-1.0.1/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;


try{
    $configurations = json_decode(file_get_contents('config.txt'),true);
}
catch (Exception $exception){
    print_r($exception->getMessage());
}

$CONSUMER_KEY = $configurations['consumer_key'];
$CONSUMER_SECRET = $configurations['consumer_secret'];
$access_token = $configurations['access_token'];
$access_token_secret = $configurations['access_token_secret'];
$FILE_NAME_PREFIX = 'target_';
$screen_name = $configurations['target_account_screen_name'];
$limit = null; //TODO change this
$current_limit = 5000;
$next_str = '-1';
$target_list_folder_path = 'target_followers_list';
$count =1;

while ($next_str!= "0"){


    $search_params = array('screen_name'=>$screen_name,'count'=>5000);
    if($next_str != '-1'){
        $search_params['cursor'] =$next_str;
    }

    $connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token, $access_token_secret);
    $content = $connection->get("followers/ids",$search_params);
    $filename = $FILE_NAME_PREFIX.$count.".txt";
    $filepath = $target_list_folder_path.DIRECTORY_SEPARATOR.$filename;

    if(file_exists($filepath)){
        unlink($filepath);
    }
    print_r("$current_limit followers saved to $filepath\n");
    file_put_contents($filepath,json_encode($content),FILE_APPEND);

    $current_limit= $current_limit+5000;
    $next_str = $content->next_cursor_str;

    if(!is_null($limit) && $current_limit > $limit){
        break;
    }
    print_r("waiting 30 seconds before getting next 5000 ids\n");
    sleep(30);
    print_r("end of waiting 30 seconds before getting next 5000 ids\n");
    $count++;

}




?>