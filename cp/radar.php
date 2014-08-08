<?php
require_once('./includes/lock.php');
require_once('./includes/conf.php');
require_once('./includes/mysql.php');

if (!isset($_GET['numlimit_r']) or $_GET['numlimit_r'] < 1) {
	$numlimit = 10;
} else {

	$numlimit = $_GET['numlimit_r'];
}

$id = $_GET['id'];
$result = DB::query('SELECT * FROM targets WHERE `id`=%s ORDER BY `lastupdate` DESC LIMIT %i', $id, $numlimit);
$count = DB::count();
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title" id="myModalLabel">Callback History for <?php print $_GET['name']; ?> (<?php print "Current time is " . date("M j, Y g:i a", time()); ?>)</h4>
</div>
<div class="modal-body">
	<div>
		<form name='radar' class="form" id="radarForm" action='index.php' method='GET'>

			<select class="form-control" onchange="$('#radarForm').submit();" name='numlimit_r' id='numlimit_r'>                  
				<option value='20' <?php if ($numlimit == 20) print 'selected'; ?>>Show previous 20</option>                    
				<option value='40' <?php if ($numlimit == 40) print 'selected'; ?>>Show previous 40</option>
				<option value='60' <?php if ($numlimit == 60) print 'selected'; ?>>Show previous 60</option>
				<option value='9999' <?php if ($numlimit == 9999) print 'selected'; ?>>Show All</option>
			</select>

			<input type='hidden' name='id' id='id' value='<?php print $_GET['id']; ?>'>
			<input type='hidden' name='name' id='name' value='<?php print $_GET['name']; ?>'>
			<input type='hidden' name='action' value='show_radar'>                    
		</form>
		<table class="table table-bordered">
			<tr>
				<th>ID</th>
				<th>External IP</th>
				<th>Hostname</th>
				<th>Last Update</th>
			</tr>

<?php
if ($count != 0) {
	foreach ($result as $row) {
		?>

					<tr>
						<td>
							<p><?php print $row['id']; ?></p>
						</td>
						<td>
							<p><?php print $row['externalip']; ?></p>
						</td>
						<td>
							<p><?php print $row['hostname']; ?></p>
						</td>
						<td>
							<p><?php print date("M j, Y g:i a", (int) $row['lastupdate']); ?></p>
						</td>
					</tr>

		<?php
	}
} else {
	?>
				<tr>
					<td colspan='5'>
				<center>No Results!</center>
				</td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
</div>