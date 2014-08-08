<?php
require_once("./includes/mysql.php");

/*
include('./includes/header.php');
include('./includes/dbconnect.php');
include('./includes/conf.php');
*/

//VERIFY USER LOGIN
if($_POST['username'] && $_POST['password'])
{
    $sql = "SELECT * FROM users WHERE `username` = '" . $mysqli->real_escape_string($_POST['username']) . "' AND `password` = '" . sha1($mysqli->real_escape_string($_POST['password'])) . "'";
    $result = $mysqli->query($sql);
    $row1 = $result->fetch_row();
    
    if(sha1($mysqli->real_escape_string($_POST['password'])) == $row1[1])
    //if($result->num_rows == 1)
    {
	//close result
	$result->close();
	
	//set session variables
  	$_SESSION["username"] = $_POST["username"];
        $_SESSION["loggedin"] = true;
	
	//update table with most recent login time
	$sql = "UPDATE users SET lastlogin = '" . time() . "' WHERE username = '" . $_SESSION['username'] . "'";
	$result = $mysqli->query($sql);
	//$result = mysql_query($sql);
	
	if(!$result)
	{
	    print 'Error updating user login information.';
	}
	//else $result->close();
	
	//redirect to main page after login
	header('Location: ./index.php');
    }
    else
    {
	$result->close();
        header('Location: ./login.php?error=true');
    }    
}
else
{
    header('Location: ./login.php?error=true');
}

include('./includes/dbclose.php');
ob_flush();
?>
