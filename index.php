<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	require_once __DIR__ . '/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;
	include("QueryMySQLRPC.php");
	$LOGFILE = "/var/tmp/frontend.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
	error_log("[{$date}] [{$file}] [{$level}] New connection started from IP {$_SERVER['REMOTE_ADDR']}".PHP_EOL, 3, $LOGFILE);
	$mysql_rpc = new MySqlRPCClient();
	if ( isset($_SESSION['user'])!="" ) {
		header("Location: home.php");
		error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} requested index.php but session is already active.  Redirecting to Homepage.".PHP_EOL, 3, $LOGFILE);
		exit;
	}
	
	$error = false;
	
	if( isset($_POST['btn-login']) ) {	
		
		// sanitizing input
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);
		
		if(empty($email)){
			$error = true;
			$emailError = "Please enter your email address.";
		} else if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "Please enter valid email address.";
		}
		
		if(empty($pass)){
			$error = true;
			$passError = "Please enter your password.";
		}
		
		if (!$error) {
			$password = hash('sha256', $pass);
			$response = $mysql_rpc->call("--LOGINQUERY--SELECT email, full_name, pw_hash, pw_reset_flag FROM Users WHERE email='$email' AND pw_hash='$password'--LOGINQUERY--");
			$sqlData = explode(",",$response);
			$count = $sqlData[0];
			$sqlEmail = $sqlData[1];
			$eqPwHash = $sqlData[2];
			$pwResFlag = $sqlData[3];
			if( $count == 1 && $eqPwHash == 1 ) {
				if(strcmp("Y",$pwResFlag) == 0){
					$_SESSION['user'] = $sqlEmail;
					error_log("[{$date}] [{$file}] [{$level}] User {$sqlEmail} signed in.  Password change is required.".PHP_EOL, 3, $LOGFILE);
					header("Location: changepwd.php");
				}
				else{
					$_SESSION['user'] = $sqlEmail;
					error_log("[{$date}] [{$file}] [{$level}] User {$sqlEmail} successfully signed in.".PHP_EOL, 3, $LOGFILE);
					header("Location: home.php");
				}
			} else {
				$errMSG = "Incorrect Credentials, Try again...";
				error_log("[{$date}] [{$file}] [Warning] User from IP {$_SERVER['REMOTE_ADDR']} tried to sign in with incorrect credentials".PHP_EOL, 3, $LOGFILE);
				
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
            	<h2 class="">Welcome to Moo-Bees!<br></h2>
				<h3 class="">Sign in to search and access your preferences<br></h3>
            </div>
        
        	<div class="form-group">
            	<hr />
            </div>
            
            <?php
			if ( isset($errMSG) ) {
				
				?>
				<div class="form-group">
            	<div class="alert alert-danger">
				<span class="glyphicon glyphicon-info-sign"></span> <?php echo $errMSG; ?>
                </div>
            	</div>
                <?php
			}
			?>
            
            <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
            	<input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo $email; ?>" maxlength="40" />
                </div>
                <span class="text-danger"><?php echo $emailError; ?></span>
            </div>
            
            <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
            	<input type="password" name="pass" class="form-control" placeholder="Your Password" maxlength="15" />
                </div>
                <span class="text-danger"><?php echo $passError; ?></span>
            </div>
            
            <div class="form-group">
            	<hr />
            </div>
            
            <div class="form-group">
            	<button type="submit" class="btn btn-block btn-primary" style="background-color:#383838; border:none" name="btn-login">Sign In</button>
            </div>
            
            <div class="form-group">
            	<hr />
            </div>
            
            <div class="form-group">
            	<a href="register.php">Create an account.</a><br>
				<a href="resetpwd_v2.php">Forgot password?</a>
            </div>
        
        </div>
   
    </form>
    </div>	

</div>

</body>
</html>
<?php ob_end_flush(); ?>
