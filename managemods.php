<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  $PAGE_TITLE = 'Manage Moderators';

  if(!checkPerms('admin')){
    $_SESSION['wfmb_failure'] = "Not authorized to access this feature.";
    header('Location: error.php');
    exit;
  }

  $db = dbInstance();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'get-user') {
      $qResult = $db->prepare("SELECT name,discord_id,type,id FROM users WHERE id = ?");
      $qResult->execute([$_POST['id']]);
      $data = $qResult->fetch(PDO::FETCH_ASSOC);
      $qResult = $db->prepare("SELECT guild FROM userguildaccess WHERE discord_id = ?");
      $qResult->execute([$data['discord_id']]);
      $guilds = $qResult->fetchAll(PDO::FETCH_COLUMN,0);
      $data['guilds'] = $guilds;
      header('Content-type: application/json');
      echo json_encode(array('result' => 'success', 'data' => $data));
    } elseif (isset($_POST['action']) & $_POST['action'] == 'add-user'){
      $qResult = $db->prepare("INSERT INTO users (name,type,discord_id) VALUES (?,?,?)");
      $qResult->execute([$_POST['name'],$_POST['role'],$_POST['discord_id']]);
      foreach ($_POST['guilds'] as $guild) {
        $qResult = $db->prepare("INSERT INTO userguildaccess (guild,discord_id) VALUES (?,?)");
        $qResult->execute([$guild,$_POST['discord_id']]);
      }
      if ($qResult){
        saveLog('add_user', $_SESSION['wfmb_admin_dID'], $_POST['name'].' - '.$_POST['discord_id'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'User added successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while adding a new user', 'data' => $_POST));
      }
    } elseif (isset($_POST['action']) & $_POST['action'] == 'edit-user'){
      $qResult = $db->prepare("UPDATE users SET name = ?, type = ?, discord_id = ? WHERE id = ?");
      $qResult->execute([$_POST['name'],$_POST['role'],$_POST['discord_id'],$_POST['id']]);
      $db->prepare("DELETE FROM userguildaccess WHERE discord_id = ?")->execute([$_POST['discord_id']]);
      foreach ($_POST['guilds'] as $guild) {
        $qResult = $db->prepare("INSERT INTO userguildaccess (guild,discord_id) VALUES (?,?)");
        $qResult->execute([$guild,$_POST['discord_id']]);
      }
      if ($qResult){
        saveLog('edit_user', $_SESSION['wfmb_admin_dID'], $_POST['name'].' - '.$_POST['discord_id'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'User modified successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while editing the user', 'data' => $_POST));
      }
    }  elseif (isset($_POST['action']) & $_POST['action'] == 'delete-user'){
      try {
        $qResult = $db->prepare("SELECT * FROM users WHERE id = ?");
        $qResult->execute([$_POST['id']]);
        $dataToDelete = $qResult->fetch(PDO::FETCH_ASSOC);
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$_POST['id']]);
        saveLog('delete_user', $_SESSION['wfmb_admin_dID'], 'discord: '.$dataToDelete['discord_id'].' - name: '.$dataToDelete['name'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'User deleted successfully','data' => $_POST));
      } catch (\Throwable $th) {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while trying to delete the user.', 'data' => $_POST));
      }
    }
    exit;
  }
  $sqlQuery = "SELECT * FROM users";
  $r = $db->prepare($sqlQuery);
  $r->execute();
  $users = $r->fetchAll(PDO::FETCH_ASSOC);
  $sqlQuery = "SELECT * FROM managedguilds";
  $r = $db->prepare($sqlQuery);
  $r->execute();
  $guilds =  $r->fetchAll(PDO::FETCH_ASSOC);
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
    <title>WFModBot - Manage Mods</title>
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
                <th>Name</th>
                <th>Discord ID</th>
                <th>Type</th>
                <th>Name in warnings</th>
                <th>Last login</th>
                <th>#</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user) {?>
              <tr>
                <td><?php echo $user['name']?></td>
                <td><?php echo $user['discord_id']?></td>
                <td><?php echo $user['type']?></td>
                <td><?php echo $user['ShowWarnName']==1 ? 'Yes' : 'No' ?></td>
                <td><?php echo gmdate("Y-m-d H:i:s", $user['last_login'])?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-warning action-button" data-id="<?php echo $user['id'] ?>" data-action="edit"><span class="material-symbols-outlined">edit</span></button>
                  <button class="btn btn-sm btn-danger action-button" data-id="<?php echo $user['id'] ?>" data-action="delete"><span class="material-symbols-outlined">delete</span></button>
                </td>
              </tr>
            <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
    <!-- Modal Manage data -->
    <div class="modal fade" id="manageUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="manageUserModalLabel">Manage User</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="manageUser-form">
              <input name="id" type="hidden" class="form-control" id="manageUser-id">
              <input name="action" type="hidden" class="form-control" id="manageUser-action" value="default">
              <div class="mb-3">
                <label for="manageUser-name" class="form-label">Name</label>
                <input name="name" type="text" class="form-control" id="manageUser-name" placeholder="User name" required>
              </div>
              <div class="mb-3">
                <label for="manageUser-discord_id" class="form-label">Discord ID</label>
                <input name="discord_id" type="text" class="form-control" id="manageUser-discord_id" placeholder="ID" required>
              </div>
              <div class="mb-3">
                <label for="manageUser-role" class="form-label">Role</label>
                <select name="role" id="manageUser-role" class="form-select ps-2">
                  <option value="admin">Admin</option>
                  <option value="moderator">Moderator</option>
                  <option value="trainee">Trainee</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="manageUser-guilds" class="form-label">Moderated Guilds</label>
                <select name="guilds[]" id="manageUser-guilds" class="form-select ps-2" multiple>
                  <?php foreach ($guilds as $guild) {
                    echo '<option value="'.$guild['guild'].'">'.$guild['name'].'</option>' ;
                  } ?>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-refreshpage="0" onclick="refreshOnClose(this)">Close</button>
            <button type="button" class="btn btn-primary" onclick="saveChanges()">Save</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Manage data -->

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/DataTables/datatables.min.js"></script>
    <script src="./assets/SweetAlert/sweetalert.js"></script>
    <script src="./assets/js/main.js"></script>
    <script>
      function saveChanges(){
        let formData = $('#manageUser-form').serialize();
        $.post('managemods.php', formData, function(data){
          if (data.result == 'success') {
            setRefreshOnModalClose();
            return popupMessage('success', data.message)
          } else {
            return popupMessage('error', data.message)
          }
        })
      }
      $(".action-button").on('click', function(event){
        let id = $(this).data('id');
        let action = $(this).data('action');
        var btn = $(this);
        if (action == 'delete') {
          confirmChoice("Are you sure you want to delete this user?", function(){
            $.post('managemods.php', { id: id, action: 'delete-user'}, function(data){
              if (data.result == 'success'){
                btn.closest('tr').remove();
                return popupMessage('success', data.message);
              }
              return popupMessage('error', data.message);
            })
          })
        } else {
          $.post('managemods.php',{ id: id, action: 'get-user'},function(data){
            $('#manageUserModalLabel').text('Edit User');
            $('#manageUser-id').val(data.data.id);
            $('#manageUser-action').val('edit-user');
            $('#manageUser-name').val(data.data.name);
            $('#manageUser-discord_id').val(data.data.discord_id);
            $('#manageUser-role').val(data.data.type);
            $('#manageUser-guilds').val(data.data.guilds);
            $('#manageUserModal').modal('show');
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
                },
                { 
                  text: '<span class="material-symbols-outlined me-1 text-sm">person_add</span> Add User', 
                  className: 'btn btn-info',
                  action: function ( e, dt, button, config ) {
                    $('#manageUserModalLabel').text('Add new user');
                    $('#manageUser-action').val('add-user');
                    $('#manageUserModal').modal('show');
                  }  
                },
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