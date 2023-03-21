<?php
session_start();
function is_logged_out(): bool
{
    $var_names = [
        'type',
        'user_id',
        'name',
        'email'
    ];
    $is_logged_out = false;
    foreach ($var_names as $var){
        if (isset($_SESSION[$var]) && $_SESSION[$var]!=''){
            continue;
        }else{
            $is_logged_out = true;
            break;
        }
    }
    return $is_logged_out;
}

function session_check(){
    if (is_logged_out()){
        logout();
    }else{
        if ($_SESSION['type']!='youlive_admin'){
            $client = getOauth2Client();
        }
    }
}

function logout(){
    session_unset();
    session_destroy();
    header('Location: index.php');
}