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

	if ( isset($_POST['btn-signup']) ) {
		
		//sanitizing input
		$name = trim($_POST['name']);
		$name = strip_tags($name);
		$name = htmlspecialchars($name);
		
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);

		$pass_conf = trim($_POST['pass_conf']);
		$pass_conf = strip_tags($pass_conf);
		$pass_conf = htmlspecialchars($pass_conf);
		
		if (empty($name)) {
			$error = true;
			$nameError = "You must enter a name.";
		} else if (!preg_match("/^[a-zA-Z ]+$/",$name)) {
			$error = true;
			$nameError = "Only characters accepted are A-Z and Spaces.";
		}
		
		if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "You must enter valid email address.";
		} else {
			// check email exist or not
			$query = "--CHECKUSREXIST--SELECT * FROM Users WHERE email='$email'--CHECKUSREXIST--"; // CHANGED userEmail to email and users to User (tbh)
			$result = $mysql_rpc->call($query);
			//$count = mysqli_num_rows($result);
			//echo $result;
			if($result=='exists'){
				$error = true;
				$emailError = "This email is already in use. Did you forget your password?";
			}
		}
		// password validation
		if (empty($pass)){
			$error = true;
			$passError = "You must enter password.";
		} else if(strlen($pass) < 6) {
			$error = true;
			$passError = "Password must have at least 6 characters.";
		} else if($pass !== $pass_conf) {
			$error = true;
			$passError = "Passwords don't match!";
		} 
		
		// password encrypt using SHA256();
		$password = hash('sha256', $pass);
		
		// if there's no error, continue to signup
		if( !$error ) {
			$mysql_rpc2 = new MySqlRPCClient();
			$query2 = "--CREATEUSER--INSERT INTO Users(full_name,email,pw_hash) VALUES('$name','$email','$password')--CREATEUSER--"; //// CHANGED users to Users, userName to full_name ,userEmail to email, userPass to pw_hash (tbh)
			$result2 = $mysql_rpc2->call($query2);
				
			if ($result2) {
				$errTyp = "success";
				$errMSG = "Registration successful! You may login now.";
				unset($name);
				unset($email);
				unset($pass);
				error_log("[{$date}] [{$file}] [{$level}] New user has been registered from IP {$_SERVER['REMOTE_ADDR']}.  The new user is {$email}.".PHP_EOL, 3, $LOGFILE);
			
			} else {
				$errTyp = "danger";
				$errMSG = "Oops! There was an unknown error, please try later.";	
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
            	<h2 class="">Sign Up.</h2>
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
                <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
            	<input type="text" name="name" class="form-control" placeholder="Enter Name" maxlength="50" value="<?php echo $name ?>" />
                </div>
                <span class="text-danger"><?php echo $nameError; ?></span>
            </div>
            
            <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
            	<input type="email" name="email" class="form-control" placeholder="Enter Your Email" maxlength="40" value="<?php echo $email ?>" />
                </div>
                <span class="text-danger"><?php echo $emailError; ?></span>
            </div>
            
            <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
            	<input type="password" name="pass" class="form-control" placeholder="Enter Password" maxlength="15" />
                </div>
                <span class="text-danger"><?php echo $passError; ?></span>
            </div>

	    <div class="form-group">
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
            	<input type="password" name="pass_conf" class="form-control" placeholder="Confirm Password" maxlength="15" />
                </div>
                <span class="text-danger"></span>
			</div>
            
            <div class="form-group">
            	<hr />
            </div>
            
            <div class="form-group">
            	<button type="submit" class="btn btn-block btn-primary" style="background-color:#383838; border:none" name="btn-signup">Complete Registration</button>
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
