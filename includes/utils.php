<?php
include_once('config.php');
error_reporting(E_ALL);
ini_set('display_errors', 'Off');
define('CURRENT_PAGE', strtolower($_SERVER['REQUEST_URI']));
define('THEME', isset($_COOKIE['wfmb_theme']) && ($_COOKIE['wfmb_theme'] == "dark") ? 'dark' : 'light');

function dbInstance(){
  try {
    //return new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    return new PDO(DB_DIALECT.":host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASS);
  } catch (Exception $e) {
    $_SESSION['wfmb_failure'] = "Cannot connect to the database: ".$e->getMessage();
    header("Location: error.php");
    exit;
  }
}

function fastFetch($db,$query,$type){
  $q = $db->prepare($query);
  $q->execute();
  if ($type == 'count') {
    return $q->fetch(PDO::FETCH_ASSOC)['amount'];
  } else {
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }
}

function checkPerms($required){
  $admtype = isset($_SESSION['wfmb_admin_type']) ? $_SESSION['wfmb_admin_type'] : 'NO_AUTH';
  if ($admtype == 'admin'){
    return TRUE;
  } else {
    return ($admtype == $required);
  }
}

function secsToDHMS($seconds) {
  $d = floor($seconds / 86400);
  $remaining = $seconds - $d * 86400;
  
  $h = floor($seconds / 3600);
  $remaining = $seconds - $h * 3600;

  $m = floor($remaining / 60);
  $remaining = $remaining - $m * 60;

  $s = round($remaining, 3); 
  $s = number_format($s, 3, '.', '');

  $h = str_pad($h, 2, '0', STR_PAD_LEFT);
  $m = str_pad($m, 2, '0', STR_PAD_LEFT);
  $s = str_pad($s, 2, '0', STR_PAD_LEFT);

  $out = $d . "d " . $h . "h " . $m . "m " . (int) $s . "s";
  return $out;
}

function clearAuthCookie() {
	unset($_COOKIE['wfmb_auth_key']);
	unset($_COOKIE['wfmb_remToken']);
	unset($_COOKIE['wfmb_loginCheck']);
	setcookie('wfmb_auth_key', '', -1, '/');
	setcookie('wfmb_remToken', '', -1, '/');
	setcookie('wfmb_loginCheck', '', -1, '/');
	setcookie('wfmb_usr', '', -1, '/');
}

function saveLog($type, $moderator, $info, $logDB=FALSE){
  if (!$logDB){
    $logDB = dbInstance();
  }
  $sqlQuery = "INSERT INTO activity_logs (type, moderator, info, date) VALUES (?,?,?,?)";
  $logDB->prepare($sqlQuery)->execute([$type, $moderator, $info, time()]);
}

?>