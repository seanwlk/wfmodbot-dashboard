<?php
  session_start();
  include_once('includes/utils.php');
  //include_once('includes/authvalidate.php');
  $PAGE_TITLE = 'Error';

  $db = dbInstance();

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
    <title>WFModBot - Error</title>
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
          <div class="d-flex justify-content-center">
            <span class="p-5 display-1">ERROR</span>
          </div>
          <div class="ps-5 pe-5">
            <?php include('includes/session_messages.php') ?>
          </div>
          <div class="d-flex justify-content-center">
            <a href="index.php">Back to home</a>
          </div>
        </div>

      </div>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/DataTables/datatables.min.js"></script>
    <script src="./assets/SweetAlert/sweetalert.js"></script>
    <script src="./assets/js/main.js"></script>
  </body>
</html>