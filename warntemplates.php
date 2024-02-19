<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');

  if(!checkPerms('admin')){
    $_SESSION['wfmb_failure'] = "Not authorized to access this feature.";
    header('Location: error.php');
    exit;
  }

  $PAGE_TITLE = 'Warning templates';

  $db = dbInstance();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) & $_POST['action'] == 'get-template') {
      $qResult = $db->prepare("SELECT * FROM warntemplates WHERE id = ?");
      $qResult->execute([$_POST['id']]);
      $data = $qResult->fetch(PDO::FETCH_ASSOC);
      header('Content-type: application/json');
      echo json_encode(array('result' => 'success', 'data' => $data));
    } elseif (isset($_POST['action']) & $_POST['action'] == 'add-template'){
      $qResult = $db->prepare("INSERT INTO warntemplates (template,message) VALUES (?,?)");
      $qResult->execute([$_POST['template'],$_POST['message']]);
      if ($qResult){
        saveLog('add_warntemplate', $_SESSION['wfmb_admin_dID'], $_POST['template'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'Warn template added successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while adding a new template', 'data' => $_POST));
      }
    } elseif (isset($_POST['action']) & $_POST['action'] == 'edit-template'){
      $qResult = $db->prepare("UPDATE warntemplates SET template = ?, message = ? WHERE id = ?");
      $qResult->execute([$_POST['template'],$_POST['message'],$_POST['id']]);
      if ($qResult){
        saveLog('edit_warntemplate', $_SESSION['wfmb_admin_dID'], $_POST['template'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'Template modified successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'An error occured while editing the template', 'data' => $_POST));
      }
    } elseif (isset($_POST['action']) & $_POST['action'] == 'delete-template'){
      if (checkPerms('admin')){
        $qResult = $db->prepare("SELECT * FROM warntemplates WHERE id = ?");
        $qResult->execute([$_POST['id']]);
        $dataToDelete = $qResult->fetch(PDO::FETCH_ASSOC);
        $db->prepare("DELETE FROM warntemplates WHERE id = ?")->execute([$_POST['id']]);
        saveLog('delete_warntemplate', $_SESSION['wfmb_admin_dID'], $dataToDelete['template'], $db);
        header('Content-type: application/json');
        echo json_encode(array('result' => 'success', 'message' => 'Template deleted successfully','data' => $_POST));
      } else {
        header('Content-type: application/json');
        echo json_encode(array('result' => 'error', 'message' => 'You dont have permissions to delete templates!', 'data' => $_POST));
      }
    } else {
      header('Content-type: application/json');
      echo json_encode(array('result' => 'error', 'message' => 'Method not found'));
    }
    exit;
  }
  $sqlQuery = "SELECT * FROM warntemplates";
  $r = $db->prepare($sqlQuery);
  $r->execute([]);
  $templates = $r->fetchAll(PDO::FETCH_ASSOC);
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
    <title>WFModBot - Warn templates</title>
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
            <table class="table w-100 app-table d-none" id="table-templates">
            <thead>
              <tr>
                <th>ID</th>
                <th>Message</th>
                <th>#</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $template) {?>
              <tr>
                <td><?php echo $template['template']?></td>
                <td><?php echo $template['message']?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-warning action-button" data-id="<?php echo $template['id'] ?>" data-action="edit"><span class="material-symbols-outlined">edit</span></button>
                  <button class="btn btn-sm btn-danger action-button" data-id="<?php echo $template['id'] ?>" data-action="delete"><span class="material-symbols-outlined">delete</span></button>
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
    <div class="modal fade" id="manageTemplateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="manageTemplateModalLabel">Manage Template</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="manageTemplate-form">
              <input name="id" type="hidden" class="form-control" id="manageTemplate-id">
              <input name="action" type="hidden" class="form-control" id="manageTemplate-action" value="default">
              <div class="mb-3">
                <label for="manageTemplate-template" class="form-label">Template shorthandle</label>
                <input name="template" type="text" class="form-control" id="manageTemplate-template" placeholder="Template ID" required>
              </div>
              <div class="mb-3">
                <label for="manageTemplate-message" class="form-label">Message</label>
                <textarea name="message" class="form-control" id="manageTemplate-message" rows="5"></textarea>
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
        let formData = $('#manageTemplate-form').serialize();
        $.post('warntemplates.php', formData, function(data){
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
          confirmChoice("Are you sure you want to delete this template?", function(){
            $.post('warntemplates.php', { id: id, action: 'delete-template'}, function(data){
              if (data.result == 'success'){
                btn.closest('tr').remove();
                return popupMessage('success', data.message);
              }
              return popupMessage('error', data.message);
            })
          })
        } else {
          $.post('warntemplates.php',{ id: id, action: 'get-template'},function(data){
            $('#manageTemplateModalLabel').text('Edit template');
            $('#manageTemplate-id').val(data.data.id);
            $('#manageTemplate-action').val('edit-template');
            $('#manageTemplate-template').val(data.data.template);
            $('#manageTemplate-message').val(data.data.message);
            $('#manageTemplateModal').modal('show');
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
                  text: '<span class="material-symbols-outlined me-1 text-sm">add_comment</span> Add template', 
                  className: 'btn btn-info',
                  action: function ( e, dt, button, config ) {
                    $('#manageTemplateModalLabel').text('Add new template');
                    $('#manageTemplate-action').val('add-template');
                    $('#manageTemplateModal').modal('show');
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