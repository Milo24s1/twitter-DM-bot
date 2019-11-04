<?php


replaceLogFile();

/**
 * when bot is running screen output is logged in 'out.txt' file.
 * after a successful round once bot is restarted , previous round output will be moved to new output file
 * new file name will be num of existing out.txt file + 1
 */
function replaceLogFile(){
    $LOG_DIR_PATH = 'logs';
    $out_files = glob($LOG_DIR_PATH.DIRECTORY_SEPARATOR.'out*txt');
    $last_file_suffix = count($out_files)+1;

    $new_file_name = $LOG_DIR_PATH.DIRECTORY_SEPARATOR.'out'.$last_file_suffix.'.txt';
    if(file_exists('out.txt')){

        $current_out_file_content = file_get_contents('out.txt');
        if(!empty($current_out_file_content)){
            file_put_contents($new_file_name,file_get_contents('out.txt'),FILE_APPEND);
        }
        unlink('out.txt');
        touch('out.txt');
    }

}

?>