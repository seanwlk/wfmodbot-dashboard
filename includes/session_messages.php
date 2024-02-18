<?php
if(isset($_SESSION['wfmb_success'])){
  echo '<div class="alert alert-success alert-dismissable">
    		'. $_SESSION['wfmb_success'].'
  	  </div>';
  unset($_SESSION['wfmb_success']);
}

if(isset($_SESSION['wfmb_failure'])){
  echo '<div class="alert alert-danger alert-dismissable">
    		'. $_SESSION['wfmb_failure'].'
  	  </div>';
  unset($_SESSION['wfmb_failure']);
}

if(isset($_SESSION['wfmb_info'])){
  echo '<div class="alert alert-info alert-dismissable">
    		'. $_SESSION['wfmb_info'].'
  	  </div>';
  unset($_SESSION['wfmb_info']);
}
?>