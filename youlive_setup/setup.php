<?php
require('./vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

const APP_NAME = "Youlive";
const THEME='dark';

try{
    $db_connection = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
        $_ENV['DB_NAME'] ?? 'youlive',
        $_ENV['DB_PORT'] ?? 3306
    );
}
catch (Exception $e){
    $error_title = 'Oops something went wrong!';
    $error_message = 'Connect with the server admin (Error Code: DB_CONNECT).';
    print_error($e, $error_title, $error_message);
    exit();
}

function print_error(Exception $e, string $title, string $message): void
{
    echo '<h1 style="text-align: center; margin: 20px 0 0 0">'.$title.'</h1><br>';
    echo '<p style="text-align: center; margin: 5px 0 20px 0">'.$message.'</p>';
    echo $_ENV['DEV_MODE']?"<dl style='margin: 0 50px'>
               <dt>Message : ".$e -> getMessage()."</dt>
               <dt>File : ". $e->getFile() ."</dt>
               <dt>Line : ". $e->getLine() ."</dt>
               <dt>Stacktrace : ". $e->getTraceAsString() ."</dt>
          </dl>":'';
}
function log_error(mysqli $db_connection, string $type, Exception $e):void{
    $query = $db_connection->prepare("INSERT INTO errorlog (type, `error`) VALUES (?, ?)");
    $query->bind_param('ss', $type, $e);
    if (!$query->execute()){
        print_error($e, "Internal Server Error: 500", "Please contact server admin (Error Code: DB_LOG)");
    }
}

