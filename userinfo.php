<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  include_once('./includes/requests/src/Autoload.php');
  WpOrg\Requests\Autoload::register();

  if(!checkPerms('moderator')){
    $_SESSION['wfmb_failure'] = "Not authorized to access this feature.";
    header('Location: error.php');
    exit;
  }

  $PAGE_TITLE = 'Search for a community member';

  $db = dbInstance();

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

  function getUserWranings($id){
    global $MODERATED_GUILD;
    global $db;
    $q = $db->prepare("SELECT * FROM warnings WHERE discord_id = ? AND guild = ?");
    $q->execute([$id,$MODERATED_GUILD]);
    return $q->fetchall(PDO::FETCH_ASSOC);
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
    <title>WFModBot - User info</title>
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
          <div class="row ps-2 pt-2 pe-2 ps-md-5 pe-md-5 pt-md-5">
            <div class="col-12 col-md-6 d-flex">
              <form action="userinfo.php" method="post">
                <div class="input-group mb-3">
                  <span class="input-group-text">Discord ID</span>
                  <input name="discord_id" type="text" class="form-control" placeholder="ID">
                  <button class="btn btn-outline-primary" type="sumbit">Search</button>
                </div>
              </form>
            </div>
          </div>
          <?php if (isset($_POST['discord_id'])) {
            $userDiscordData = getDiscordGuildMember($_POST['discord_id']);
            if(!isset($userDiscordData['guildMember']->user->id)){
              echo '<div class="row ps-2 pt-2 pe-2 ps-md-5 pe-md-5 pt-md-5 h4"><div class="col-12">User not found in the server</div></div>';
            } else {
              $userWarnings = getUserWranings($_POST['discord_id']);
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
                  Warnings
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered" id="warningsdataTable" width="100%" cellspacing="0">
                      <thead>
                        <tr>
                          <th>Mod</th>
                          <th>Reason</th>
                          <?php if(checkPerms('admin')){ ?>
                            <th>#</th>
                          <?php } ?>
                        </tr>
                      </thead>
                      <tbody>
                      <?php foreach($userWarnings as $warn) { ?>
                        <tr>
                          <td><?php echo $warn['moderator'] ?></td>
                          <td><?php echo htmlentities(substr($warn['reason'], 1, -1)) ?> </td>
                          <?php if(checkPerms('admin')){ ?>
                          <td class="text-nowrap">
                            <button class="btn btn-sm btn-danger action-button" data-id="<?php echo $warn['id'] ?>" data-action="delete"><span class="material-symbols-outlined">delete</span></button>
                          </td>
                          <?php } } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <?php }
            saveLog('user_info', $_SESSION['wfmb_admin_dID'], 'info about: '.$_POST['discord_id'], $db);
          } ?>
        </div>

      </div>
    </div>
    

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/DataTables/datatables.min.js"></script>
    <script src="./assets/SweetAlert/sweetalert.js"></script>
    <script src="./assets/js/main.js"></script>
    <script>
      $(".action-button").on('click', function(event){
        let id = $(this).data('id');
        let action = $(this).data('action');
        var btn = $(this);
        if (action == 'delete') {
          confirmChoice("Are you sure you want to delete this warning?", function(){
            $.post('warnings.php', { id: id, action: 'delete-warning'}, function(data){
              if (data.result == 'success'){
                btn.closest('tr').remove();
                return popupMessage('success', data.message);
              }
              return popupMessage('error', data.message);
            })
          })
        }
      });
    </script>
  </body>
</html>