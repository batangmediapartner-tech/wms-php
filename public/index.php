<?php
session_start();

define('BASE_PATH', realpath(__DIR__ . '/../'));

   
require_once BASE_PATH."/config/database.php";
require_once BASE_PATH."/core/auth.php";
require_once BASE_PATH."/core/helper.php";


$module = isset($_GET['module']) ? $_GET['module'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

$path = "../modules/" . $module . "/" . $action . ".php";

	
if (file_exists($path)) {
    require $path;
} else {
    echo "Module tidak ditemukan.";
}
?>