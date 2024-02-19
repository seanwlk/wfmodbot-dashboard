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

  $PAGE_TITLE = 'Send Message';

  $db = dbInstance();

  function sendMessage($channel,$msg) {
    $headers = array(
      'Authorization' => 'Bot '.DISCORD_BOT_TOKEN,
      'User-Agent' => 'WFModBot ('.APP_URL.', v1.0)',
      'Content-Type' => 'application/json'
    );
    $request = WpOrg\Requests\Requests::post('https://discord.com/api/channels/'.$channel.'/messages', $headers, json_encode(array('content'=> $msg)));
    $data = json_decode($request->body, true);
    
    if($data['code'] == 10003){
      $request = WpOrg\Requests\Requests::post('https://discord.com/api/users/@me/channels',$headers,json_encode(array('recipient_id' => $channel)));
      $request = json_decode($request->body);
      
      $request = WpOrg\Requests\Requests::post('https://discord.com/api/channels/'.$request->id.'/messages',$headers,json_encode(array('content'=> $msg)));
      $data = json_decode($request->body, true);
    } 
    if (!isset($data['code'])) {
      return array('resp' => TRUE);
    } elseif ($data['code'] == 50013) {
      return array('resp' => FALSE, 'msg' => 'Missing permissions to send message to this channel');
    } else {
      return array('resp' => FALSE, 'msg' => 'Unknown message response state: '.$data['code']);
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'send-message') {
      $result = sendMessage($_POST['channel'],$_POST['message']);
      if ($result['resp']){
        header('Content-type: application/json');
        saveLog('send_message', $_SESSION['wfmb_admin_dID'], $_POST['channel'].' - '.$_POST['message'], $db);
        echo json_encode(array('result' => 'success', 'message' => 'Message sent successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => $result['msg'], 'data' => $_POST));
      }
    } else {
      header('Content-type: application/json');
      echo json_encode(array('result' => 'error', 'message' => 'Method not found'));
    }
    exit;
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
    <title>WFModBot - Send Message</title>
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
          <div class="container pt-4">
            <form id="send-message-form">
              <input type="hidden" name="action" value="send-message">
              <div class="mb-3">
                <label for="channelId" class="form-label">Channel ID / User ID</label>
                <input name="channel" type="text" class="form-control" id="channelId" placeholder="ID" value="<?php isset($_GET['user']) ? $_GET['user'] : ''?>">
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Example textarea</label>
                <textarea name="message" class="form-control" id="message" rows="4"></textarea>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary" type="submit"><span class="material-symbols-outlined me-2">send</span>Send</button>
              </div>
            </form>
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
      $(document).ready(function() {
        $('#send-message-form').on('submit', function(e){
          e.preventDefault();
          $.post('sendmessage.php', $('#send-message-form').serialize(), function(data){
              if (data.result == 'success'){
                return popupMessage('success', data.message);
              }
              return popupMessage('error', data.message);
            })
        });
      });
    </script>
  </body>
</html>