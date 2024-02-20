<?php 
if ( !isset($_SESSION['wfmb_loggedin']) ||  !isset($_SESSION['wfmb_admin_type'])) {
  $_SESSION['wfmb_redirect'] = CURRENT_PAGE;
  header('Location:login.php');
}
if (isset($_SESSION['wfmb_loggedin'])){
  // SET current MODERATED_GUILD and get GUILD permissions
  if (!isset($_SESSION['wfmb_currentGuild'])) {
    $db = dbInstance();
    $userGuildAccess = $db->prepare("SELECT * FROM userguildaccess WHERE discord_id = ?");
    $userGuildAccess->execute([$_SESSION['wfmb_admin_dID']]);
    $userGuildAccess = $userGuildAccess->fetchAll(PDO::FETCH_ASSOC);
    if (count($userGuildAccess) == 0) {
      $_SESSION["wfmb_currentGuild"] = "0";
      $MODERATED_GUILD = "0";
    }else {
      $MODERATED_GUILD = $userGuildAccess[0]['guild'];
      $_SESSION["wfmb_currentGuild"] = $userGuildAccess[0]['guild'];
    }
  } else {
    $MODERATED_GUILD = $_SESSION["wfmb_currentGuild"];
  }
}
?>