<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');

  if(!checkPerms('admin')){
    $_SESSION['wfmb_failure'] = "Not authorized to access this feature.";
    header('Location: error.php');
    exit;
  }

  $PAGE_TITLE = 'Moderator Dashboard Activity Logs';

  $db = dbInstance();

  $sqlQuery = "SELECT activity_logs.id, activity_logs.type, activity_logs.info, activity_logs.date, users.name AS moderator
  FROM activity_logs
  LEFT JOIN users ON users.discord_id = activity_logs.moderator 
  ORDER BY activity_logs.id DESC";
  $r = $db->prepare($sqlQuery);
  $r->execute([]);
  $logs = $r->fetchAll(PDO::FETCH_ASSOC);
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
    <title>WFModBot - Activity Logs</title>
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
          <div class="row p-2 table-responsive">
            <table class="table w-100 app-table d-none" id="table-logs">
            <thead>
              <tr>
                <th>Moderator</th>
                <th>Action</th>
                <th>Info</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log) {?>
              <tr>
                <td><?php echo $log['moderator']?></td>
                <td><?php echo $log['type']?></td>
                <td><?php echo $log['info']?></td>
                <td><?php echo gmdate("Y-m-d H:i:s", $log['date'])?></td>
              </tr>
            <?php } ?>
              </tbody>
            </table>
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
        $('.app-table').each((idx,el) => {
          $(el).DataTable( {
            dom: 'Bfrtip',
            buttons: {
              buttons: [
                {
                  text: '<span class="material-symbols-outlined me-1 text-sm">file_download</span> Excel', 
                  extend: 'excel', 
                  className: 'btn btn-success',
                  exportOptions: { columns: 'th:not(:last-child)' } 
                }
              ]
            },
            pageLength: 30,
            order: [],
            sort: true
          });
          $(el).removeClass('d-none');
        });
      });
    </script>
  </body>
</html>