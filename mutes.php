<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  $PAGE_TITLE = 'Muted users';

  $db = dbInstance();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'delete-mute'){
      if (TRUE){
        //$data = $qResult->fetch(PDO::FETCH_ASSOC);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'Utente non trovato', 'data' => $_POST));
      }
    }
    exit;
  }
  $sqlQuery = "SELECT mutes.id, mutes.discord_id, mutes.username, mutes.when_unmute, mutes.date, users.name AS moderator
  FROM mutes
  LEFT JOIN users ON users.discord_id = mutes.moderator 
  WHERE guild = ".$MODERATED_GUILD
  ." ORDER BY mutes.id DESC";
  $r = $db->prepare($sqlQuery);
  $r->execute([]);
  $mutes = $r->fetchAll(PDO::FETCH_ASSOC);
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
    <title>WFModBot - Mutes</title>
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
            <table class="table w-100 app-table d-none" id="table-mutes">
            <thead>
              <tr>
                <th>Discord ID</th>
                <th>User</th>
                <th>Moderator</th>
                <th>Until</th>
                <th>#</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($mutes as $mute) {?>
              <tr>
                <td><?php echo $mute['discord_id']?></td>
                <td><?php echo substr($mute['username'], 1, -1)?></td>
                <td><?php echo $mute['moderator']?></td>
                <td><?php echo $mute['when_unmute'] != "permanent" ? gmdate("Y-m-d H:i:s", $mute['when_unmute']) : "Permanent" ?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-danger action-button" data-id="<?php echo $mute['id'] ?>" data-action="delete"><span class="material-symbols-outlined">delete</span></button>
                </td>
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
      $(".action-button").on('click', function(event){
        let id = $(this).data('id');
        let action = $(this).data('action');
        var btn = $(this);
        if (action == 'delete') {
          confirmChoice("Are you sure you want to delete this mute?", function(){
            $.post('mutes.php', { id: id, action: 'delete-mute'}, function(data){
              if (data.result == 'success'){
                btn.closest('tr').remove();
                return popupMessage('success', data.message);
              }
              return popupMessage('error', data.message);
            })
          })
        }
      });
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
            pageLength: 20,
            order: [],
            sort: true
          });
          $(el).removeClass('d-none');
        });
      });
    </script>
  </body>
</html>