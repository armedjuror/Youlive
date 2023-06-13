<?php
require 'requirements.php';
require SETUP_FILE;
require 'session.php';
require 'oauth-helper.php';


$method = $_SERVER['REQUEST_METHOD'];
$path_string = rtrim(substr(@$_SERVER['PATH_INFO'], 1), '/');
$path = explode("/", $path_string);
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

