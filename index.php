<?php
  session_start();
  include_once('includes/utils.php');
  include_once('includes/authvalidate.php');
  include_once('./includes/requests/src/Autoload.php');
  WpOrg\Requests\Autoload::register();
  function getDiscordUserName($id) {
    $headers = array(
      'Authorization' => 'Bot '.DISCORD_BOT_TOKEN,
      'User-Agent' => 'WFModBot ('.APP_URL.', v1.0)',
      'Content-Type' => 'application/json'
    );
    $request = WpOrg\Requests\Requests::get('https://discordapp.com/api/v6/users/'.$id, $headers);
    $user = json_decode($request->body);
    return isset($user->username) ? $user->username : '@NONAME';
  }

  $db = dbInstance();

  $warnings =  fastFetch($db,"SELECT count(*) AS amount FROM warnings WHERE guild =".$MODERATED_GUILD,'count');
  $moderators =  fastFetch($db,"SELECT count(*) AS amount FROM userguildaccess uga LEFT JOIN users ON users.discord_id = uga.discord_id WHERE users.enabled = 1 AND uga.guild = ".$MODERATED_GUILD,'count');
  $bans =  fastFetch($db,"SELECT count(*) AS amount FROM bans WHERE guild =".$MODERATED_GUILD,'count');
  $warningOverviewArray = fastFetch($db,"SELECT users.name as moderator ,count(warnings.moderator) AS amount FROM warnings LEFT JOIN users ON users.discord_id= warnings.moderator WHERE users.enabled = 1 AND users.name IS NOT NULL AND  guild =".$MODERATED_GUILD." GROUP BY users.name,warnings.moderator",'fetchall');
  $activeMutes = fastFetch($db,"SELECT username,date,when_unmute FROM mutes WHERE when_unmute != 'permanent' AND guild =".$MODERATED_GUILD." ORDER BY when_unmute ASC",'fetchall');
  $topwarned = fastFetch($db,"SELECT discord_id,count(discord_id) AS amount FROM warnings WHERE guild =".$MODERATED_GUILD." AND discord_id NOT IN (SELECT discord_id FROM mutes WHERE when_unmute = \"permanent\") GROUP BY discord_id ORDER BY amount DESC LIMIT 5",'fetchall');

  $modsGraph = array();
  $modwarnGraph = array();
  foreach ($warningOverviewArray as $mod) {
    array_push($modsGraph,$mod['moderator']);
    array_push($modwarnGraph,$mod['amount']);
  }

  $topwarnedNames = array();
  $topwarnedAmount = array();

  foreach ($topwarned as $user){
    array_push($topwarnedNames, getDiscordUserName($user['discord_id']));
    array_push($topwarnedAmount, $user['amount']);
  }
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
    <title>WFModBot</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
  </head>
  <body>
    
    <?php include ('includes/sidebar.php') ?>
    <div class="content-wrapper">
    <?php include ('includes/navbar.php')?>
      <div class="container-fluid">
        
        <div class="page-content p-4">
          <!-- STAT CARDS-->
          <div class="row row-cols-1 row-cols-md-4 g-4">
            <div class="col">
              <div class="card shadow border border-5 border-end-0 border-top-0 border-bottom-0 border-success">
                <div class="card-body">
                  <div class="row">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Moderators</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $moderators ?></div>
                    </div>
                    <div class="col-auto">
                    <span class="material-symbols-outlined text-success me-3 h1">group</span>
                    </div>
                  </div>
                  <a href="managemods.php" class="small">View details</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card shadow border border-5 border-end-0 border-top-0 border-bottom-0 border-warning">
                <div class="card-body">
                  <div class="row">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Warnings</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $warnings ?></div>
                    </div>
                    <div class="col-auto">
                    <span class="material-symbols-outlined text-warning me-3 h1">warning</span>
                    </div>
                  </div>
                  <a href="warnings.php" class="small">View details</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card shadow border border-5 border-end-0 border-top-0 border-bottom-0 border-danger">
                <div class="card-body">
                  <div class="row">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Bans</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $bans ?></div>
                    </div>
                    <div class="col-auto">
                    <span class="material-symbols-outlined text-danger me-3 h1">gavel</span>
                    </div>
                  </div>
                  <a href="bans.php" class="small">View details</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card shadow border border-5 border-end-0 border-top-0 border-bottom-0 border-info">
                <div class="card-body">
                  <div class="row">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Mutes</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($activeMutes) ?></div>
                    </div>
                    <div class="col-auto">
                    <span class="material-symbols-outlined text-info me-3 h1">volume_off</span>
                    </div>
                  </div>
                  <a href="mutes.php" class="small">View details</a>
                </div>
              </div>
            </div>
          </div>
          <!-- STAT CARDS-->
          <!-- GRAPH CARDS-->
          <div class="row graph-row mt-4">
            <div class="col-12 col-md-8">
              <div class="card shadow h-100">
                <div class="card-header">
                  Warnings Overview
                </div>
                <div class="card-body">
                  <canvas id="warningsChart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-4 mt-md-0">
              <div class="card shadow h-100">
                <div class="card-header">
                  Most warned users
                </div>
                <div class="card-body">
                  <canvas id="mostWarned"></canvas>
                </div>
              </div>
            </div>
          </div>
          <!-- GRAPH CARDS-->
          <!-- MUTES+COMMANDS CARDS-->
          <div class="row mt-4">
            <div class="col-12 col-md-6">
              <div class="card shadow h-100">
                <div class="card-header">
                Mutes expiring soon<span class="float-end small">Remaining</span>
                </div>
                <div class="card-body">
                <?php 
                $i = 0;
                foreach ($activeMutes as $row) {
                  $i++;
                  $unmuteTime = $row['when_unmute'] - $row['date'];
                  $currentProgress = time() - $row['date'];
                  $percent = (int) (($currentProgress * 100)/$unmuteTime) ;
                ?>
                  <h4 class="small font-weight-bold"><?php echo str_replace("'", "", $row['username']) ?> <span class="float-end"><?php echo ($percent < 100) ? secsToDHMS($row['when_unmute']-time())." (".$percent."%)" : "Mute Expired" ?></span></h4>
                  <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percent ?>%" ></div>
                  </div>
                <?php 
                if ($i > 8) break;
                } ?>

                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 mt-4 mt-md-0">
              <div class="card shadow h-100">
                <div class="card-header">
                  WFModBot Commands
                </div>
                <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                      <tr>
                        <th scope="col">Command</th>
                        <th scope="col">Description</th>
                        <th scope="col">Arguments</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Warn</td>
                        <td>Sends a warnings to the user</td>
                        <td>[1] User mention or ID<br>[2] Warning text</td>
                      </tr>
                      <tr>
                        <td>Mute</td>
                        <td>Assigns mute role to the user</td>
                        <td>[1] User mention or ID<br>[2] mute time</td>
                      </tr>
                      <tr>
                        <td>Unmute</td>
                        <td>Removes mute from user</td>
                        <td>User mention or ID</td>
                      </tr>
                      <tr>
                        <td>Kick</td>
                        <td>Kicks user out of the server</td>
                        <td>[1] User mention or ID <br>[2] Reason</td>
                      </tr>
                      <tr>
                        <td>Ban</td>
                        <td>Ban user from the server</td>
                        <td>[1] User mention or ID <br>[2] Reason</td>
                      </tr>
                      <tr>
                        <td>Warnings</td>
                        <td>Shows warnings of a user</td>
                        <td>User mention or ID</td>
                      </tr>
                      <tr>
                        <td>Moderations</td>
                        <td>Shows active mutes and remaining time</td>
                        <td>None</td>
                      </tr>
                      <tr>
                        <td>Purge</td>
                        <td>Deletes said amount of messages</td>
                        <td>[1] User mention or ID (Optional) <br>[2] Amount</td>
                      </tr>
                      <tr>
                        <td>reload</td>
                        <td>Reloads all the configs from db [Admin only]</td>
                        <td>None</td>
                      </tr>
                    </tbody>
                  </table>
                  </div>


                </div>
              </div>
            </div>
          </div>
          <!-- MUTES+COMMANDS  CARDS-->
        </div>

      </div>
    </div>
    

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/chart.js/Chart.min.js"></script>
    <script src="./assets/js/main.js"></script>
    <script>
      function number_format(number, decimals, dec_point, thousands_sep) {
        // *     example: number_format(1234.56, 2, ',', ' ');
        // *     return: '1 234,56'
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
          prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
          sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
          dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
          s = '',
          toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
          };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
          s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
          s[1] = s[1] || '';
          s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
      }

      var ctx = document.getElementById("warningsChart");
      var warningsChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($modsGraph);?>,
          datasets: [{
            label: "Warnings",
            backgroundColor: "#4e73df",
            hoverBackgroundColor: "#2e59d9",
            borderColor: "#4e73df",
            data: <?php echo json_encode($modwarnGraph);?>,
          }],
        },
        options: {
          maintainAspectRatio: false,
          layout: {
            padding: {
              left: 10,
              right: 25,
              top: 25,
              bottom: 0
            }
          },
          scales: {
            xAxes: [{
              time: {
                unit: 'warns'
              },
              gridLines: {
                display: false,
                drawBorder: false
              },
              maxBarThickness: 25,
            }],
            yAxes: [{
              ticks: {
                min: 0,
                maxTicksLimit: 5,
                padding: 10,
                callback: function(value, index, values) {
                  return number_format(value);
                }
              },
              gridLines: {
                color: "rgb(234, 236, 244)",
                zeroLineColor: "rgb(234, 236, 244)",
                drawBorder: false,
                borderDash: [2],
                zeroLineBorderDash: [2]
              }
            }],
          },
          legend: {
            display: false
          },
          tooltips: {
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
            callbacks: {
              label: function(tooltipItem, chart) {
                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
              }
            }
          },
        }
      });


      var ctx = document.getElementById("mostWarned");
      var mostWarned = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: <?php echo json_encode($topwarnedNames);?>,
          datasets: [{
            data: <?php echo json_encode($topwarnedAmount);?>,
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc','#D16BA5','#D69355'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
          }],
        },
        options: {
          maintainAspectRatio: false,
          tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
          },
          legend: {
            display: true
          },
          cutoutPercentage: 80,
        },
      });
    </script>
  </body>
</html>