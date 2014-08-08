<?php

ob_start();

if(!isset($_SESSION))
{
    session_start();
}

require_once('./includes/conf.php');

if(!$_SESSION['username'] || !$_SESSION['loggedin'])
{
    header("Location: ./login.php");
    exit(); //MUST EXIT!!!
}
?>