<?php
session_start();
require 'requirements.php';
require SETUP_FILE;

$method = $_SERVER['REQUEST_METHOD'];
$path = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
$controller = strtolower($path[0]);
$function = strtolower($path[1] ?? 'main');
$request = new Request($method, array_slice($path, 2));
$request->connection = $db_connection;
$controller_path = 'api/' . $controller . '.controller.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
    if (function_exists($function))
        call_user_func_array($function, [$request]);
    else {
        echo json_encode(array('status_code'=>404, 'error_code'=>'404', 'error_desc'=>'Route not found!', 'error_msg'=>'Oops, not found!'));
        exit();
    }
} else {
    echo json_encode(array('status_code'=>404, 'error_code'=>'404', 'error_desc'=>'Controller not found!', 'error_msg'=>'Oops, not found!'));
    exit();
}

