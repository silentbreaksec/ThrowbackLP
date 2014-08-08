<?php
require_once('./includes/lock.php');
require_once('./includes/conf.php');
require_once('./includes/mysql.php');

if (!isset($_GET['numlimit']) or $_GET['numlimit'] < 1) {
	$numlimit = 15;
} else {

	$numlimit = $_GET['numlimit'];
}

$id = $_GET['id'];
$result = DB::query('SELECT * FROM tasks WHERE id = %s ORDER BY opentime DESC LIMIT %d', $id, $numlimit);
$count = DB::count();
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title" id="myModalLabel">History for <?php print $_GET['name']; ?> (<?php print "Current time is " . date("M j, Y g:i a", time()); ?>)</h4>
</div>
<div class="modal-body">
	<div>
		<form name='history' class="form" id="historyForm" action='index.php' method='GET'>

			<select class="form-control" onchange="$('#historyForm').submit();" name='numlimit' id='numlimit'>
				<option value='20' <?php if ($numlimit == 20) print 'selected'; ?>>Show previous 20</option>
				<option value='40' <?php if ($numlimit == 40) print 'selected'; ?>>Show previous 40</option>
				<option value='60' <?php if ($numlimit == 60) print 'selected'; ?>>Show previous 60</option>
				<option value='9999' <?php if ($numlimit == 9999) print 'selected'; ?>>Show All</option>
			</select>

			<input type='hidden' name='id' id='id' value='<?php print $_GET['id']; ?>'>
			<input type='hidden' name='name' id='name' value='<?php print $_GET['name']; ?>'>
			<input type='hidden' name='action' value='show_history'>                    
		</form>
		<table class="table table-bordered">
			<tr>
				<th>Action</th>
				<th>Command</th>
				<th>Arguments</th>
				<th>Date</th>
			</tr>

<?php
if ($count != 0) {
	//while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
	foreach ($result as $row) {
		?>

					<tr id='rowHeader'>
						<td>
							<p>
					<?php
					print $row['type'];
					?>
							</p>

						</td>
						<td>
							<p><?php print $row['command']; ?></p>
						</td>
						<td>
							<p><?php print $row['arguments']; ?></p>
						</td>
						<td>
							<p>
		<?php
		$show = false;
		if ($row['closetime'])
			print date("M j, Y g:i a", $row['closetime']);
		else
			print '<button type="button" onclick="location.href=\'deletetask.php?name=' . $_GET['name'] . '&id=' . $row['id'] . '&time=' . $row['opentime'] . '\';" class="btn btn-default btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span> Cancel</button> Waiting for delivery..';
		?>
							</p>
						</td>
					</tr>

					<tr id='rowData'>
						<td colspan='4'>
							<p>
		<?php
		if ($row['results']) {
			//print strlen($row['results']);
			if (strlen($row['results']) < 100)
				$rows = 2;
			elseif (strlen($row['results']) < 400)
				$rows = 5;
			elseif (strlen($row['results']) < 650)
				$rows = 9;
			elseif (strlen($row['results']) < 950)
				$rows = 15;
			else
				$rows = 20;

			print "<textarea class='form-control' style='width: 100%;border: 0; background-color: #EDEDED;' cols='100' rows='$rows' readonly='readonly' >";
			print base64_decode($row['results']);
			print "</textarea>";
		}
		else {
			print "<textarea class='form-control' style='width: 100%;border: 0; background-color: #EDEDED;' cols='100' rows='2' readonly='readonly'>";
			print "Processing...";
			print "</textarea>";
		}
		?>
							</p>
						</td>
					</tr>

		<?php
	}
} else {
	?>
				<tr>
					<td colspan='4'>
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
