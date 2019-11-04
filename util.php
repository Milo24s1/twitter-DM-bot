<?php

function main(){
    $tried_text_file_name = 'tried1.txt';
    $completed_this_round_file_name = 'completed_in_this_round.txt';

    $tried_text = file_get_contents($tried_text_file_name);
    $tried_id_numbers = json_decode($tried_text);


    print_r("all tried numbers: ".count($tried_id_numbers)."\n");

    $unique_tried_numbers = array_unique($tried_id_numbers);

    print_r("unique tried numbers:".count($unique_tried_numbers)."\n");

    $completed_in_this_round_text = explode(",",file_get_contents($completed_this_round_file_name));
    $completed_in_this_id_numbers = $completed_in_this_round_text;
    array_pop($completed_in_this_id_numbers);
    print_r("this round tried numbers: ".count($completed_in_this_id_numbers)."\n");

    print_r("first element ".$completed_in_this_id_numbers[0]."\n");
    print_r("last element ".$completed_in_this_id_numbers[245]."\n");

    $unique_tried_numbers = array_unique($completed_in_this_id_numbers);

    print_r("this round unique tried numbers: ".count($unique_tried_numbers)."\n");


    $all_ids = array_merge($tried_id_numbers,$completed_in_this_id_numbers);

    print_r("all id numbers ".count($all_ids)."\n");

    $uniqe_all_ids = array_unique($all_ids);

    print_r("all id numbers after unique".count($uniqe_all_ids)."\n");


    unlink($tried_text_file_name);
    touch($tried_text_file_name);

    file_put_contents($tried_text_file_name,$uniqe_all_ids,0);

    $tried_text = file_get_contents($tried_text_file_name);
    $tried_id_numbers = json_decode($tried_text);


    print_r("all tried numbers after writing file: ".count($tried_id_numbers)."\n");
}

function run(){

    $tried_text_file_name = 'tried1.txt';
    $completed_this_round_file_name = 'completed_in_this_round.txt';

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
    file_put_contents( 'tried1.txt', json_encode( $tried_id_numbers ) ,0);

    $tried_text = file_get_contents($tried_text_file_name);
    $tried_id_numbers = json_decode($tried_text);
    print_r("tried # of users after write :".count($tried_id_numbers)."\n");




}

run();

?>
