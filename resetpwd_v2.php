<?php
	ob_start();
	session_start();
	if( isset($_SESSION['user'])!="" ){
		header("Location: home.php");
	}
	require_once __DIR__ . '/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;
	include("QueryMySQLRPC.php");
	$LOGFILE = "/var/tmp/frontend.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
	
	$mysql_rpc = new MySqlRPCClient();
	$error = false;
	error_log("[{$date}] [{$file}] [{$level}] Password Reset page accessed from {$_SERVER['REMOTE_ADDR']}.".PHP_EOL, 3, $LOGFILE);
			
	if ( isset($_POST['btn-resetpw']) ) {
		
		//sanitizing input
		
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		
		
		if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "You must enter valid email address.";
		} else {
			// check email exist or not
			$query = "--CHECKUSREXIST--SELECT * FROM Users WHERE email='$email'--CHECKUSREXIST--"; // CHANGED userEmail to email and users to User (tbh)
			$result = $mysql_rpc->call($query);
			//$count = mysqli_num_rows($result);
			//echo $result;
			if($result=='notexists'){
				$error = true;
				$emailError = "This email address is not in our system, please try again.";
				error_log("[{$date}] [{$file}] [Warning] User from IP {$_SERVER['REMOTE_ADDR']} tried to reset password of a non-existent account.".PHP_EOL, 3, $LOGFILE);
	
			}
		}
				
		// if there's no error, continue to signup
		if( !$error ) {
			$bytes = openssl_random_pseudo_bytes(4);
			$newpwd = bin2hex($bytes);
			$newpwdhash = hash('sha256', $newpwd);
			$mysql_rpc2 = new MySqlRPCClient();
			$query2 = "--CREATEUSER--UPDATE Users SET pw_hash='$newpwdhash' WHERE email='$email'--CREATEUSER--"; //// CHANGED users to Users, userName to full_name ,userEmail to email, userPass to pw_hash (tbh)
			$result2 = $mysql_rpc2->call($query2);
			error_log("[{$date}] [{$file}] [{$level}] A password was reset from IP {$_SERVER['REMOTE_ADDR']} for user {$email}.".PHP_EOL, 3, $LOGFILE);
	
			$query3 = "--CREATEUSER--UPDATE Users SET pw_reset_flag='Y' WHERE email='$email'--CREATEUSER--";
			$mysql_rpc2->call($query3);
			error_log("[{$date}] [{$file}] [{$level}] Setting passwor change flag to ON for user {$email}.".PHP_EOL, 3, $LOGFILE);
			$msg = "Hello there!\n\nThis is your new password to access Moobees. You will be forced to change it next time you sign in.\n\nNew Password: " . $newpwd;
			$msg = wordwrap($msg,70);
			$headers = 'From: MooBees' . "\r\n" . 'Reply-To: do-not-reply@moobees.local';
			mail($email,"Your New Password",$msg,$headers);	
			if ($result2) {
				$errTyp = "success";
				$errMSG = "Password has been reset! You may now login using your new password.";
				error_log("[{$date}] [{$file}] [{$level}] Email sent to User {$email} regarding password change.".PHP_EOL, 3, $LOGFILE);
				unset($name);
				unset($email);
				unset($pass);
				
			
			} else {
				$errTyp = "danger";
				$errMSG = "Oops! There was an unknown error, please try later.";	
				error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} changed passwords but an email couldn't be sent: Unspecified error from MySQL server.".PHP_EOL, 3, $LOGFILE);
			
			}	
				
		}
		
		
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Moo-Bees!</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<header>
	<div id="header">
</header>
<body>

<div class="container">

	<div id="login-form">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    
    	<div class="col-md-12">
        
        	<div class="form-group">
            	<h2 class="">Reset Password.</h2>
            </div>
        
        	<div class="form-group">
            	<hr />
            </div>
            
            <?php
			if ( isset($errMSG) ) {
				
				?>
				<div class="form-group">
            	<div class="alert alert-<?php echo ($errTyp=="success") ? "success" : $errTyp; ?>">
				<span class="glyphicon glyphicon-info-sign"></span> <?php echo $errMSG; ?>
                </div>
            	</div>
                <?php
			}
			?>
            
            <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
            	<input type="email" name="email" class="form-control" placeholder="Enter Your Email" maxlength="40" value="<?php echo $email ?>" />
                </div>
                <span class="text-danger"><?php echo $emailError; ?></span>
            </div>
            
            <div class="form-group">
            	<hr />
            </div>
            
            <div class="form-group">
            	<button type="submit" class="btn btn-block btn-primary" style="background-color:#383838; border:none" name="btn-resetpw">Reset Password</button>
            </div>
            
            <div class="form-group">
            	<hr />
            </div>
            
            <div class="form-group">
            	<a href="index.php">Back to sign in...</a>
            </div>
        
        </div>
   
    </form>
    </div>	

</div>

</body>
</html>
<?php ob_end_flush(); ?>
