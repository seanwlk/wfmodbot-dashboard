<?php
session_start();
include ('includes/utils.php');

if (isset($_GET['logout']) && $_GET['logout']){
  clearAuthCookie();
  session_destroy();
  header('Location:login.php');
	exit;
}

if (isset($_COOKIE['wfmb_auth_key']) && isset($_COOKIE['wfmb_remToken']) && isset($_COOKIE['wfmb_loginCheck'])) {

	//Get user credentials from cookies.
	$auth_key = filter_var($_COOKIE['wfmb_auth_key']);
	$remToken = filter_var($_COOKIE['wfmb_remToken']);
	$loginCheck = filter_var($_COOKIE['wfmb_loginCheck']);
	
	$db = dbInstance();
  $qResult = $db->prepare('SELECT * FROM users WHERE auth_key = ?');
	$qResult->execute([$auth_key]);
	$user = $qResult->fetch(PDO::FETCH_ASSOC);
	
	if ($qResult->rowCount() > 0) {
		if (password_verify($remToken, $user['remtoken']) && password_verify($loginCheck, $user['logincheck'])) {
		  
		  $loginExpires = strtotime($user['login_expires']);
		  if(time() > $loginExpires){
				clearAuthCookie();
				header('Location:login.php');
				exit;
			}
		  
			$_SESSION['wfmb_loggedin'] = TRUE;
			$_SESSION['wfmb_admin_type'] = $user['type'];
			$_SESSION['wfmb_username'] = $user['name'];
			$_SESSION['wfmb_admin_dID'] = $user['discord_id'];
			$_SESSION['wfmb_avatar'] = $user['avatar'];

			$db->prepare("UPDATE users SET last_login = ? WHERE id = ?")->execute([time(), $user['id']]);
			header('Location:'.(isset($_SESSION["wfmb_redirect"]) ? $_SESSION["wfmb_redirect"] :"index.php"));
      unset($_SESSION["wfmb_redirect"]);
			exit;
		} else {
			clearAuthCookie();
			header('Location:login.php');
			exit;
		}
	} else {
		clearAuthCookie();
		header('Location:login.php');
		exit;
	}
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
    <title>WFModBot - Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
  </head>
  <body class="bg-primary">
    <div class="container">
      <div class="row justify-content-center">
        <div class="card border-0 shadow-lg my-5"  style="width: 500px;">
          <div class="card-body">
            <!-- Nested Row within Card Body -->
            <div class="row text-center">
              <div class="p-5">
                <div class="text-center">
                  <h1 class="h4 text-gray-900 mb-4">Login to WFModBot Dashboard</h1>
                </div>
                <img class="mx-auto d-block" src="assets/img/wfmod.png">
                <hr>
                <?php include('includes/session_messages.php') ?>
                <a href="https://discordapp.com/api/oauth2/authorize?client_id=604927756861440000&redirect_uri=<?php echo urlencode(APP_URL."/oauth2.php")?>&response_type=code&scope=identify" class="btn btn-primary mx-auto">
                  <i class="fab fa-discord fa-fw"></i> Login with Discord
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/main.js"></script>

  </body>
</html>
