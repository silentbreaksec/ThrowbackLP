<?php

ob_start();
require_once('./includes/lock.php');
require_once('./includes/conf.php');
require_once('./includes/mysql.php');

$id = $_GET['id'];
$time = $_GET['time'];
$name = $_GET['name'];

DB::delete('tasks', '`id`=%s AND `opentime`=%s', $id, $time);

header("Location: index.php?action=show_history&id=" . $id);
exit;
//print "<META HTTP-EQUIV='Refresh' CONTENT='.1; URL=history.php?name=" . urlencode($name) . "&id=" . $id . "'>";

?>