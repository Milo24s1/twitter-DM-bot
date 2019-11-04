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

$completed_in_this_round = 'completed_in_this_round.txt';
$success_in_this_round = 'success_in_this_round.txt';
$all_success_file = 'all_success_count.txt';
$tried_text= 'tried.txt';
$target_list_folder_path = 'target_followers_list';
$FILE_NAME_PREFIX = 'target_';
$current_limit = 5000;
$messageTextContent = file_get_contents('message.txt');



$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token, $access_token_secret);
$content = $connection->get("account/verify_credentials");
welcomeMessage($content);

$success = 0;
$errorsCount = 0;



appendThiRoundUserIdsToTriedText($tried_text,$completed_in_this_round);
overWriteSuccessFile($success_in_this_round,$all_success_file);


$target_files = array_reverse(glob($target_list_folder_path.DIRECTORY_SEPARATOR.$FILE_NAME_PREFIX."*"));

foreach ($target_files as $target_file){

    $user_ids = json_decode(file_get_contents($target_file),true)['ids'];
    print_r("follower count  in $target_file :".count($user_ids)."\n");
    $user_ids = getDiff($user_ids);
    print_r("actual untried follower count in $target_file :".count($user_ids)."\n");
    if(count($user_ids)>0){

        print_r("start processing followers in $target_file file \n");
        break;
    }

}


$isTempAccountLocked = false;
$concective_request_before_blocked =0;
foreach ($user_ids as $key => $userId) {
    // $userId = '3145021238';
    print_r("processing record :".$key."\n");
    file_put_contents($completed_in_this_round,$userId.',',FILE_APPEND);
    $data = [
        'event' => [
            'type' => 'message_create',
            'message_create' => [
                'target' => [
                    'recipient_id' => trim($userId)
                ],
                'message_data' => [
                    'text' => $messageTextContent
                ]
            ]
        ]
    ];


    try {

        $result = $connection->post('direct_messages/events/new', $data, true);
        $concective_request_before_blocked++;

        if(property_exists($result, "errors")){
            $errorsCount++;
            print_r("You cannot send messages to this user: ".$userId."\n");
            $errorsList = $result->errors;
            foreach ($errorsList as $key => $er) {
                print_r($er->code);
                print_r("\n");
                if($er->code==226){
                    print_r("twitter identified the bot, hence sleeping for 30min , started at :".date('Y-m-d H:i:s')."\n");
                    print_r("$concective_request_before_blocked requests were sent consecutively before being blocled\n");
                    sleep(1800);
                    print_r("sleeping ended at :".date('Y-m-d H:i:s')."\n");
                    $concective_request_before_blocked =0;
                }
                else if($er->code==326){
                    print_r("twitter identified the bot, hence stopping bot at :".date('Y-m-d H:i:s')."\n");
                    print_r("326 – To protect our users from spam and other malicious activity, this account is temporarily locked. Please log in to https://twitter.com to unlock your account.\n");
                    $isTempAccountLocked = true;
                    break;
                }
            }
        }
        elseif (property_exists($result,"event")) {
            $success++;
            print_r("successfully sent to user: ".$userId."\n");
            print_r("total number of successfully sent users: ".$success."\n");
            file_put_contents($success_in_this_round,$success.":".date('Y-m-d')."\n".',',FILE_APPEND);

        }
        else{
            print_r('*** something strange happened*******\n');
            print_r('*** RESTART BOT IN ONE HOUR*******\n');
            print_r($result);
            break;
        }

        if($isTempAccountLocked){
            break;
        }


        $sleeptuime = mt_rand(10,40);
        print_r("\nsleep $sleeptuime seconds\n");
        sleep($sleeptuime);

    }
    catch (Exception $e) {
        print_r("Exception \n");
        $errorsCount++;
        print_r("You cannot send messages to this user: ".$userId."\n");
        $sleeptuime = mt_rand(10,40);
        print_r("\nsleep $sleeptuime seconds\n");
        sleep($sleeptuime);
    }



}

saveDiff($user_ids);

function getDiff($user_ids)
{

    $string_data = file_get_contents("tried.txt");
    $alreay_tried = json_decode($string_data);
    if(is_array($alreay_tried)>0){
        return array_diff($user_ids, $alreay_tried);

    }
    else{
        return $user_ids;
    }

}

function saveDiff($user_ids_diff){

    $string_data = file_get_contents("tried.txt");
    $alreay_tried = json_decode($string_data);

    $combined_user_ids =array_merge($alreay_tried,$user_ids_diff);
    file_put_contents( 'tried.txt', json_encode( $combined_user_ids) );
}



function welcomeMessage($result){

    $name = $result->name;
    $screenName = $result->screen_name;
    print_r("***********************************************************\n");
    print_r("*                      Twitter Bot                        *\n");
    print_r("*               following account will be used            *\n");
    print_r(" $name($screenName)\n");
    print_r("*                                                         *\n");
    print_r("***********************************************************\n");
}



/**
 * when bot sends a message successfully  output is logged in 'success_in_this_round.txt' file.
 * after completing a  round once bot is restarted , last line of previous round success output file  will be appended to main file
 * main file name will be "all_success_count.txt"
 */
/**
 * @param $success_in_this_round
 * @param $all_success_file
 */
function overWriteSuccessFile($success_in_this_round,$all_success_file){

    $current_success_file_conetent = file_get_contents($success_in_this_round);
    $numbers =  explode(",",$current_success_file_conetent);

    while (count($numbers) && true){

        $number = array_pop($numbers);
        if(!empty($number)){
            print_r($number."messages sent successfully in last round\n");
            break;
        }
    }


    $formatted_string =  implode(",",array_map(function ($value){return trim($value);},array_reverse(explode(':',$number))))."\n";

    unlink($success_in_this_round);
    touch($success_in_this_round);

    file_put_contents($all_success_file,$formatted_string,FILE_APPEND);
}


/**
 * when bot perform an action on speficic user, that user id is logged in 'completed_in_this_round.txt' file.
 * after completing a round once bot is restarted , 'completed_in_this_round.txt' file  contend will be appended to main file
 * main file name will be "tried.txt"
 * this "tied.txt" file content will help bot to track the user id that bot has already tried to send messages in previous rounds
 */
/***
 * @param $tried_text
 * @param $completed_in_this_round
 */
function appendThiRoundUserIdsToTriedText($tried_text_file_name,$completed_this_round_file_name){


//    copy tried.txt to as a backup
    copy($tried_text_file_name,'logs'.DIRECTORY_SEPARATOR.'backup_tried.txt');

    $tried_text = file_get_contents($tried_text_file_name);
    $tried_id_numbers = json_decode($tried_text);
    print_r("tried # of users before write :".count($tried_id_numbers)."\n");

    if(!is_array($tried_id_numbers)){
        $tried_id_numbers = array();
    }


    $completed_in_this_round_text = explode(",",file_get_contents($completed_this_round_file_name));
    $completed_in_this_id_numbers = $completed_in_this_round_text;
    array_pop($completed_in_this_id_numbers);
    if(!is_array($completed_in_this_id_numbers)){
        $completed_in_this_id_numbers = array();
    }
    print_r("this round tried # of users: ".count($completed_in_this_id_numbers)."\n");

    $tried_id_numbers = array_merge($tried_id_numbers,$completed_in_this_id_numbers);
    file_put_contents( $tried_text_file_name, json_encode( $tried_id_numbers ) ,0);

    $tried_text = file_get_contents($tried_text_file_name);
    $tried_id_numbers = json_decode($tried_text);
    print_r("tried # of users after write :".count($tried_id_numbers)."\n");


    unlink($completed_this_round_file_name);
    touch($completed_this_round_file_name);

}

?>