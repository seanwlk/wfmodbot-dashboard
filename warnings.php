<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  $PAGE_TITLE = 'Warnings';

  $db = dbInstance();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'delete-warning'){
      if (checkPerms('admin')){
        $qResult = $db->prepare("SELECT * FROM warnings WHERE id = ?");
        $qResult->execute([$_POST['id']]);
        $dataToDelete = $qResult->fetch(PDO::FETCH_ASSOC);
        $db->prepare("DELETE FROM warnings WHERE id = ?")->execute([$_POST['id']]);
        saveLog('delete_warning', $_SESSION['wfmb_admin_dID'], 'from user: '.$dataToDelete['discord_id'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'Warning deleted successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'You dont have permissions to delete warnings!', 'data' => $_POST));
      }
    }
    exit;
  }
  $sqlQuery = "SELECT warnings.id, warnings.discord_id, warnings.username, warnings.reason, warnings.date, users.name AS moderator
  FROM warnings
  LEFT JOIN users ON users.discord_id = warnings.moderator 
  WHERE guild = ".$MODERATED_GUILD.
  (isset($_GET['user']) ? ' AND warnings.discord_id = ? ' : ' AND warnings.discord_id IS NOT ? ')
  ."ORDER BY id DESC";
  $r = $db->prepare($sqlQuery);
  $r->execute([$_GET['user'] ]);

  $warnings = $r->fetchAll(PDO::FETCH_ASSOC);
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
    <title>WFModBot - Warnings</title>
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
            <table class="table w-100 app-table d-none" id="table-warnings">
            <thead>
              <tr>
                <th>ID</th>
                <th>Discord ID</th>
                <th>User</th>
                <th>Reason</th>
                <th>Moderator</th>
                <th>Date</th>
                <?php echo checkPerms('admin') ? '<th>#</th>' : '' ?>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($warnings as $warning) {?>
              <tr>
                <td><?php echo $warning['id']?></td>
                <td><?php echo $warning['discord_id']?></td>
                <td><?php echo substr($warning['username'], 1, -1)?></td>
                <td class="text-wrap"><?php echo htmlentities(substr($warning['reason'], 1, -1)) ?></td>
                <td><?php echo $warning['moderator']?></td>
                <td><?php echo gmdate("Y-m-d H:i:s", $warning['date'])?></td>
                <?php if(checkPerms('admin')){ ?>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-danger action-button" data-id="<?php echo $warning['id'] ?>" data-action="delete"><span class="material-symbols-outlined">delete</span></button>
                </td>
                <?php } ?>
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
            order: [],
            sort: false,
          });
          $(el).removeClass('d-none');
        });
      });
    </script>
  </body>
</html>