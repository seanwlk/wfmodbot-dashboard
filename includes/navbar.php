<nav class="navbar navbar-expand-lg bg-body-transparent">
  <div class="container-fluid">
    <a class="navbar-brand ps-4" href="#"><?php echo isset($PAGE_TITLE) ? $PAGE_TITLE : ' Dashboard' ?></a>
    <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto me-0">

        <li class="nav-item ">
          <span class="me-2"><?php echo isset($_SESSION['wfmb_username']) ? $_SESSION['wfmb_username'] : ''?></span>
            <img src="https://cdn.discordapp.com/avatars/<?php echo isset($_SESSION['wfmb_admin_dID']) ? $_SESSION['wfmb_admin_dID'] : ''?>/<?php echo isset($_SESSION['wfmb_avatar']) ? $_SESSION['wfmb_avatar'] : ''?>.png" class="p-0 rounded-circle" height="40px">
        </li>
      </ul>
    </div>
  </div>
</nav>