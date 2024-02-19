<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  include_once('./includes/requests/src/Autoload.php');
  WpOrg\Requests\Autoload::register();

  $PAGE_TITLE = 'My profile';

  $db = dbInstance();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'set-showwarnname') {
      if(filter_var($_POST['value'], FILTER_VALIDATE_BOOLEAN)) {
        $val = 1;
      } else {
        $val = 0;
      }
      $qResult = $db->prepare("UPDATE users SET ShowWarnName = ? WHERE discord_id = ?");
      $qResult->execute([$val,$_SESSION['wfmb_admin_dID']]);
      if ($qResult){
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'Changed the Show warning name config successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while setting this config', 'data' => $_POST));
      }
    } else {
      header('Content-type: application/json');
      echo json_encode(array('result' => 'error', 'message' => 'Method not found'));
    }
    exit;
  }

  function getDiscordGuildMember($id) {
    global $MODERATED_GUILD;
    $headers = array(
      'Authorization' => 'Bot '.DISCORD_BOT_TOKEN,
      'User-Agent' => 'WFModBot ('.APP_URL.', v1.0)',
      'Content-Type' => 'application/json'
    );
    $request = WpOrg\Requests\Requests::get('https://discordapp.com/api/v6/guilds/'.$MODERATED_GUILD.'/members/'.$id, $headers);
    $user = json_decode($request->body);
    $request = WpOrg\Requests\Requests::get('https://discordapp.com/api/v6/guilds/'.$MODERATED_GUILD.'/roles', $headers);
    $guildRoles = json_decode($request->body, true);
    return array('guildMember' => $user, 'guildRoles' => $guildRoles);
  }

?>
<html lang="en" data-bs-theme="<?php echo THEME; ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Warface Community Discord Moderator Bot">
    <link rel="shortcut icon" type="image/png" href="assets/img/wfmod.png" />
    <meta name="theme-color" content="#00d4ff">
    <meta name="author" content="seanwlk">
    <meta content='<?php echo APP_URL ?>/assets/img/wfmod.png' property='og:image'>
    <title>WFModBot - My profile</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/DataTables/datatables.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
  </head>
  <body>
    
    <?php include ('includes/sidebar.php') ?>
    <div class="content-wrapper">
    <?php include ('includes/navbar.php')?>
      <div class="container-fluid">
        
        <div class="page-content">
          <?php 
            $userDiscordData = getDiscordGuildMember($_SESSION['wfmb_admin_dID']);
            $userDb = $db->prepare("SELECT * FROM users WHERE discord_id= ?");
            $userDb->execute([$_SESSION['wfmb_admin_dID']]);
            $userDb = $userDb->fetch(PDO::FETCH_ASSOC);
            ?>
          <div class="row ps-2 pt-2 pe-2 ps-md-5 pe-md-5 pt-md-5">
            <div class="col-12 col-lg-4">
              <div class="card shadow">
                <div class="card-header text-primary h4 text-center">
                  <?php echo isset($userDiscordData['guildMember']->user->global_name) ? $userDiscordData['guildMember']->user->global_name : $userDiscordData['guildMember']->user->username  ?>
                </div>
                <div class="card-body">
                  <div class="text-center">
                    
                  <img src="https://cdn.discordapp.com/avatars/<?php echo $userDiscordData['guildMember']->user->id ?>/<?php echo $userDiscordData['guildMember']->user->avatar ?>.png" class="rounded-circle" onError="this.onerror=null;this.src='assets/img/noavatar.png';" height="150px">
                  </div>
                    <p class="card-text">
                    <ul>
                      <li>ID: <?php echo $userDiscordData['guildMember']->user->id ?></li>
                      <li>Server nick: <?php echo isset($userDiscordData['guildMember']->nick) ? $userDiscordData['guildMember']->nick : 'not set'  ?></li>
                      <li>Join date: <?php echo $userDiscordData['guildMember']->joined_at ?></li>
                      <li><?php echo isset($userDiscordData['guildMember']->premium_since) ? 'Nitro boosting' : "Not Nitro Boosting" ?></li>
                      <li><?php echo $userDiscordData['guildMember']->mute ? 'Muted in audio channels' : "Not muted in audio channels" ?></li>
                      <li><?php echo $userDiscordData['guildMember']->deaf ? 'Deafened in audio channels' : "Not deafened in audio channels" ?></li>
                    </ul>
                    <a href="#" class="btn btn-outline-primary">Send DM</a>
                  </p>
                </div>
                <div class="card-footer text-body-secondary">
                <?php 
                  foreach ($userDiscordData['guildRoles'] as $role){
                    if (in_array($role['id'], $userDiscordData['guildMember']->roles) ){
                      echo '<span class="badge text-dark" style="background-color: #'.dechex($role['color']).' ">'.$role['name'].'</span> ';
                    }
                  }?>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-8 pt-2 pt-lg-0">

              <div class="card shadow mb-4">
                <div class="card-header text-primary h4 ">
                  Dashboard
                </div>
                <div class="card-body">
                  <h5 class="pt-2">Info</h5>
                  <ul>
                    <li><b>User access type:</b> <?php echo $_SESSION['wfmb_admin_type'] ?></li>
                    <li><b>Oauth2 Discord ID:</b> <?php echo $_SESSION['wfmb_admin_dID'] ?></li>
                    <li><b>Dashboard nickname:</b> <?php echo $_SESSION['wfmb_username'] ?></li>
                    <li><b>Last login:</b> <?php echo gmdate("Y-m-d H:i:s", $userDb['last_login']) ?></li>
                    <li><b>Session exipres:</b> <?php echo $userDb['login_expires'] ?></li>
                  </ul>
                  <h5 class="pt-2">Configs</h5>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="showWarnName" <?php echo $userDb['ShowWarnName'] == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="showWarnName">Show moderator name in user warnings</label>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
    

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/DataTables/datatables.min.js"></script>
    <script src="./assets/SweetAlert/sweetalert.js"></script>
    <script src="./assets/js/main.js"></script>
    <script>
      $('#showWarnName').change(function() {
        $.post('profile.php', {value: this.checked, action: 'set-showwarnname'}, function(data){
          if (data.result == 'success') {
            return popupMessage('success', data.message)
          } else {
            return popupMessage('error', data.message)
          }
        })
      });
    </script>
  </body>
</html>