<?php
const SETUP_DIR = '../youlive_setup'; // L1
const SETUP_FILE = SETUP_DIR.'/setup.php';
const CLIENT_SECRET_JSON = SETUP_DIR.'/client_secret.json';
const SETUP_DIR_L2 = '../'.SETUP_DIR;
const SETUP_FILE_L2 = SETUP_DIR_L2.'/setup.php';
const SETUP_DIR_L3 = '../'.SETUP_DIR_L2;
const SETUP_FILE_L3 = SETUP_DIR_L3.'/setup.php';


spl_autoload_register('auto_loader');

function auto_loader($class_name)
{
    $path = './classes/';
    $extension = '.class.php';
    $full_path = $path . $class_name . $extension;

    if (!file_exists($full_path)) {
        return false;
    }

    require_once $full_path;
}