<?php

require_once('./includes/lock.php');

session_destroy();

header('Location: ./login.php');

?>