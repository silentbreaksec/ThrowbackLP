<?php
ob_start();
session_start();
session_destroy();
session_start();

require_once('./includes/mysql.php');
require_once('./includes/conf.php');

$error = '';

//DO LOGIN FORM
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$username = $_POST['username'];
	$password = sha1($_POST['password']);
	$count = 0;

	//FIND USERNAME AND PASSWORD
	DB::query("SELECT * FROM users WHERE username = %s and password = %s", $username, $password);
	$count = DB::count();

	//IF ONE RESULT IS FOUND, THEN USERNAME AND PASSWORD ARE CORRECT
	if ($count == 1) {
		$_SESSION["username"] = $_POST["username"];
		$_SESSION["loggedin"] = true;

		header("location: ./index.php");
	} else {
		$error = '<div class="alert alert-danger">Invalid Login!</div>';
	}
}
?>
<html>
    <head>
<?php include_once('./includes/header.inc.php') ?>
		<title>Throwback Control Panel</title>
    </head>
    <body>

		<!-- Fixed navbar -->
		<div class="navbar navbar-default" role="navigation"  style="margin-top: 15px;">
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
					<a class="navbar-brand"><img style="margin-top: -15px; margin-left: 350px; height: 100px" src="./images/tb.jpg"/></a>
				</div>
			</div>
		</div>      

		<div id='login' class="container" style="max-width: 400px; margin: auto; ">
			<form action='' class="form" method='post' style="margin-top: 20px;">
<?php echo $error; ?>
				<p>Username: <input class="form-control" type="text" name="username" size="25" /></p>
				<p>Password: <input class="form-control" type="password" name="password" size="25" /></p>
				<p><input type="submit" class="btn btn-primary" name="login" value="Go" /></p>
			</form>
				<?php
//LOG THE IP FOR EVEN COMING HERE!
				$ipaddress = $_SERVER['REMOTE_ADDR'];
				$date = date("M j, Y g:i a", time());
				$ref = 'unknown';

//CHECK IF THERE IS A REFERRER
				if (isset($_SERVER['HTTP_REFERER']))
					$ref = $_SERVER['HTTP_REFERER'];

//INSERT IP ACCESSING THIS PAGE!
				DB::insert('access', array('ipaddress' => $ipaddress, 'date' => $date, 'referrer' => $ref));
				?>

		</div>
	</body>
</html>
<?php
ob_flush();
?>