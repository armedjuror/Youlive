<?php
require 'vendor/autoload.php';
session_start();

$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;

function is_logged_out(): bool
{
    $var_names = [
        'type',
        'id',
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

function session_check(mysqli $db_connection){
    if (is_logged_out()){
        logout();
    }else{
        if ($_SESSION['type']!='youlive_admin'){
            $client = getOauth2Client($db_connection);
            return $client;
        }
    }
}

function logout(){
    session_unset();
    session_destroy();
    header('Location: index.php');
}