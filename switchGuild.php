<?php
session_start();
include_once('includes/utils.php');
include_once('includes/authvalidate.php');

if (isset($_GET['guildID'])){
  //get user moderated guilds 
  // set requested guildID only if in the list
  $db = dbInstance();
  $userGuildAccess = $db->prepare("SELECT guild FROM userguildaccess WHERE discord_id = ?");
  $userGuildAccess->execute([$_SESSION['wfmb_admin_dID']]);
  $userGuildAccess = $userGuildAccess->fetchAll(PDO::FETCH_COLUMN,0);
  if (in_array ($_GET['guildID'], $userGuildAccess)){
    $_SESSION["wfmb_currentGuild"] = $_GET['guildID'];
  }
  header('location: index.php');
  exit;
} else {
	header("Content-type: application/json");
	die(json_encode(array("error"=>"Method Not allowed")));
}

?>