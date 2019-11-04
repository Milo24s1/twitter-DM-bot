<?php



main($argv);

/**
 * this script can  clone existing bot codebase into new code base
 * how to run this script
 * go to root folder of existing bot & run following command
 *
 * php clone-bot.php <PUT_NEW_BOT_NAME_HERE>
 *
 */
/**
 *
 * @param $argv
 */
function main($argv){

    if(count($argv)>1){
        $new_bot_name = $argv[1];
        print_r("new bot files wll be cloned in $new_bot_name folder\n");

        $current_bot_dir = dirname(__FILE__);

        $dir_parts = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
        array_pop($dir_parts);
        $dir_parts[]= $new_bot_name;
        $new_bot_dir = implode(DIRECTORY_SEPARATOR,$dir_parts);
        print_r("copying files to new bot location\n");
        shell_exec("cp -r $current_bot_dir $new_bot_dir");



        print_r("Reset \"out.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'out.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'out.txt');

        print_r("Reset \"tried.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'tried.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'tried.txt');

        print_r("Reset \"all_success_count.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'all_success_count.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'all_success_count.txt');


        print_r("Reset \"completed_in_this_round.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'completed_in_this_round.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'completed_in_this_round.txt');

        print_r("Reset \"success_in_this_round.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'success_in_this_round.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'success_in_this_round.txt');

        print_r("Reset \"config.txt\" file\n");

        unlink($new_bot_dir.DIRECTORY_SEPARATOR.'config.txt');
        touch($new_bot_dir.DIRECTORY_SEPARATOR.'config.txt');

        $configs = array(
          "access_token"=>"<PUT_YOUR_ACCESS_TOKEN>",
          "access_token_secret"=>"<PUT_YOUR_ACCESS_TOKEN_SECRET>",
          "target_account_screen_name"=>"<PUT_YOUR_TARGET_ACCOUNT_SCREEN_NAME>",
        );

        file_put_contents($new_bot_dir.DIRECTORY_SEPARATOR.'config.txt',json_encode($configs),FILE_APPEND);

        print_r("Reset \"logs\" folder\n");

        $files = glob($new_bot_dir.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }


    }
    else{
        print_r("Please specify new bot name\n");
    }

}

?>