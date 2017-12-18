<?php

	session_start();
	$LOGFILE = "/var/tmp/frontend.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
	
	
	if (!isset($_SESSION['user'])) {
		header("Location: index.php");
	} else if(isset($_SESSION['user'])!="") {
		header("Location: home.php");
	}
	
	if (isset($_GET['logout'])) {
		error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} has signed out.".PHP_EOL, 3, $LOGFILE);
		unset($_SESSION['user']);
		session_unset();
		session_destroy();
		header("Location: index.php");
		exit;
	}
	

?>