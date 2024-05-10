<?php
session_start();
include_once('includes/utils.php');

function randomString($n) {
	$generated_string = "";
	$domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	$len = strlen($domain);
	// Loop to create random string
	for ($i = 0; $i < $n; $i++) {
		// Generate a random index to pick characters
		$index = rand(0, $len - 1);
		// Concatenating the character in resultant string
		$generated_string = $generated_string . $domain[$index];
	}
	return $generated_string;
}

function getSecureRandomToken($n) {
	$token = bin2hex(openssl_random_pseudo_bytes($n));
	return $token;
}

include('./includes/requests/src/Autoload.php');
WpOrg\Requests\Autoload::register();

if (isset($_GET['code'])) {
	
	$data = array(
		"grant_type" => "authorization_code",
		"client_id" => DISCORD_CLIENT_ID,
		"client_secret" => DISCORD_CLIENT_SECRET,
		"redirect_uri" => APP_URL."/oauth2.php",
		"code" => $_GET['code']
	);
	$request = WpOrg\Requests\Requests::post('https://discordapp.com/api/oauth2/token', array("Content-Type" => "application/x-www-form-urlencoded"), $data);
	$token = json_decode($request->body);

	$access_token = $token->access_token;
	$request = WpOrg\Requests\Requests::get('https://discordapp.com/api/users/@me', array("Authorization" => "Bearer {$access_token}"));
	$user = json_decode($request->body);

	$db = dbInstance();
	
	$qResult = $db->prepare("SELECT * FROM users WHERE discord_id = ? AND enabled = 1");
	$qResult->execute([$user->id]);
	$userDb = $qResult->fetch(PDO::FETCH_ASSOC);

	if ($qResult->rowCount() > 0) {
		
		$_SESSION['wfmb_loggedin'] = TRUE;
		$_SESSION['wfmb_admin_type'] = $userDb['type'];
		$_SESSION['wfmb_username'] = $userDb['name'];
		$_SESSION['wfmb_admin_dID'] = $userDb['discord_id'];
		$_SESSION['wfmb_avatar'] = $user->avatar;

		$auth_key = randomString(20);
		$remToken = getSecureRandomToken(16);
		$loginCheck = getSecureRandomToken(12);
		$encryted_remToken = password_hash($remToken,PASSWORD_DEFAULT);
		$encryted_loginCheck = password_hash($loginCheck,PASSWORD_DEFAULT);

		$expiry_time = date('Y-m-d H:i:s', strtotime(' + 30 days'));
		$expires = strtotime($expiry_time);
		setcookie('wfmb_usr', $userDb['name'],$expires, "/");		
		setcookie('wfmb_auth_key', $auth_key, $expires, "/");
		setcookie('wfmb_remToken', $remToken, $expires, "/");
		setcookie('wfmb_loginCheck', $loginCheck, $expires, "/");

		$db->prepare("UPDATE users SET avatar = ?, auth_key = ?, remtoken = ?, logincheck = ?, login_expires = ?, last_login = ? WHERE id = ?")->execute([$user->avatar,$auth_key,$encryted_remToken,$encryted_loginCheck,$expiry_time,time(),$userDb['id']]);
		
		//Authentication successfull redirect user
		header('Location:'.(isset($_SESSION["wfmb_redirect"]) ? $_SESSION["wfmb_redirect"] :"index.php"));
      	unset($_SESSION["wfmb_redirect"]);
		exit;

	} else {
		$_SESSION['wfmb_failure'] = "User " . $user->username ." not allowed to login.";
		header('Location:login.php');
	}
	
} else {
	header("Content-type: application/json");
	die(json_encode(array("error"=>"Method Not allowed")));
}
?>