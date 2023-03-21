<?php
require('requirements.php');
require(SETUP_FILE);
try{
    $check_channel = $db_connection->query("SELECT COUNT(*) as channel_count FROM channels")->fetch_assoc();
    if ($check_channel['channel_count']){
        header('Location: login.php');
    }else{
        header('Location: initial_setup.php');
    }
}catch (Exception $e){
    log_error($db_connection, 'DB_SELECT', $db_connection->error);
    print_error($db_connection->error, 'Oops, something went wrong!', 'This can be a runtime error. If the error persist, contact server admin. (Error Code: DB_SELECT)');
    exit();
}

