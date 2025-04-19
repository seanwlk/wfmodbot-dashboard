<div class="sidebar-menu shadow-lg" id="sidebar-menu">
  <div class="row">
    <div class="col-12"><img src="assets/img/wfmod.png" class="rounded mx-auto d-block pt-3" height="70px"></div>
    <div class="col-12 h4 pt-3 text-center text-white">WFModBot</div>
  </div>
  <div class="app-list">

      <a href="index.php" class="app-item <?php echo (CURRENT_PAGE == '/index.php' || CURRENT_PAGE == '/' ) ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">dashboard</span>Dashboard
      </a>
      <div class="app-item" type="button" data-bs-toggle="collapse" data-bs-target="#guildAccordion">
        <span class="material-symbols-outlined me-2">dns</span>Guild
        <span class="material-symbols-outlined ms-auto">expand_more</span>
      </div>
      <div id="guildAccordion" class="accordion-collapse collapse">
        <div class="app-sublist">
        <?php 
          $guildsAvailable = fastFetch($db,"SELECT managedguilds.guild,managedguilds.name FROM userguildaccess 
          LEFT JOIN managedguilds ON managedguilds.guild = userguildaccess.guild
          WHERE userguildaccess.discord_id = '".(isset($_SESSION['wfmb_admin_dID']) ? $_SESSION['wfmb_admin_dID'] : '0')."'",'fetchall');
          foreach ($guildsAvailable as $guild) {
            echo '<a href="switchGuild.php?guildID='.$guild['guild'].'" class="app-subitem '.(isset($_SESSION["wfmb_currentGuild"]) && $_SESSION["wfmb_currentGuild"] == $guild['guild'] ? 'active' : '').'">'.$guild['name'].'</a>';
          }?>
        </div>
      </div>
      <hr class="text-info m-0 ms-2 me-2">
      <a href="warnings.php" class="app-item <?php echo CURRENT_PAGE == '/warnings.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">warning</span>Warnings
      </a>
      <a href="mutes.php" class="app-item <?php echo CURRENT_PAGE == '/mutes.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">volume_off</span>Mutes
      </a>
      <a href="bans.php" class="app-item <?php echo CURRENT_PAGE == '/bans.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">gavel</span>Bans
      </a>
      <a href="blockedwordlist.php" class="app-item <?php echo CURRENT_PAGE == '/blockedwordlist.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">description</span>Blocked words
      </a>
      <?php if (checkPerms('moderator')) { ?>
      <a href="userinfo.php" class="app-item <?php echo CURRENT_PAGE == '/userinfo.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">group</span>User info
      </a>
      <?php } ?>
      <?php if (checkPerms('moderator')) { ?>
      <a href="sendmessage.php" class="app-item <?php echo CURRENT_PAGE == '/sendmessage.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">send</span>Send Message
      </a>
      <?php } ?>
      <?php if (checkPerms('admin')) { ?>
      <hr class="text-info m-0 ms-2 me-2">
      <div class="text-center text-white small mt-2">ADMIN</div>
      <a href="managemods.php" class="app-item <?php echo CURRENT_PAGE == '/managemods.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">admin_panel_settings</span>Moderators
      </a>
      <a href="warntemplates.php" class="app-item <?php echo CURRENT_PAGE == '/warntemplates.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">edit_note</span>Warn templates
      </a>
      <a href="modlogs.php" class="app-item <?php echo CURRENT_PAGE == '/modlogs.php' ? 'active' : ''?>">
        <span class="material-symbols-outlined me-2">article</span>Activity Logs
      </a>
      <?php } ?>
      <hr class="text-info m-0 ms-2 me-2">
      <div class="app-item" type="button" data-bs-toggle="collapse" data-bs-target="#profileAccordion">
        <span class="material-symbols-outlined me-2">person</span>My profile
        <span class="material-symbols-outlined ms-auto">expand_more</span>
      </div>
      <div id="profileAccordion" class="accordion-collapse collapse">
        <div class="app-sublist">
          <a href="profile.php" class="app-subitem">
            <span class="material-symbols-outlined ms-2">person</span><?php echo isset($_SESSION['wfmb_username']) ? $_SESSION['wfmb_username'] : ''?>
          </a>
          <div class="app-sublist">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="themeSelector" <?php echo THEME == 'dark' ? 'checked' : ''?>>
              <label class="form-check-label text-dark" for="themeSelector">Dark theme</label>
              <script>
              document.getElementById("themeSelector").addEventListener('change', function() {
                if (this.checked) {
                  document.documentElement.dataset.bsTheme ='dark'
                  setCookie("wfmb_theme", "dark", 365);
                } else {
                  document.documentElement.dataset.bsTheme ='light'
                  setCookie("wfmb_theme", "light", 365);
                }
              });
            </script>
            </div>
          </div>
          <a href="login.php?logout=true" class="app-subitem">
            <span class="material-symbols-outlined ms-2">logout</span>Logout
          </a>
        </div>
      </div>

      

  </div>
</div>