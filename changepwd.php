<?php
	ob_start();
	session_start();
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

	if ( isset($_POST['btn-changepw']) ) {
		
		//sanitizing input
		
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
				
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);
		
		$pass_conf = trim($_POST['pass_conf']);
		$pass_conf = strip_tags($pass_conf);
		$pass_conf = htmlspecialchars($pass_conf);
		
		
		if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "You must enter valid email address.";
		} else if (strcmp($email,$_SESSION['user']) !== 0){
			$error = true;
			$emailError = "The email entered doesn't match you account.";
			error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} attempted to change her password but email entered doesn't match.".PHP_EOL, 3, $LOGFILE);
		} else {
			// check email exist or not
			$query = "--CHECKUSREXIST--SELECT * FROM Users WHERE email='$email'--CHECKUSREXIST--"; // CHANGED userEmail to email and users to User (tbh)
			$result = $mysql_rpc->call($query);
			//$count = mysqli_num_rows($result);
			//echo $result;
			if($result=='notexists'){
				$error = true;
				$emailError = "This email address is not in our system, please try again.";
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
		
		// if there's no error, continue
		if( !$error ) {
			$newpwdhash = hash('sha256', $pass);
			$mysql_rpc2 = new MySqlRPCClient();
			$query2 = "--CREATEUSER--UPDATE Users SET pw_hash='$newpwdhash' WHERE email='$email'--CREATEUSER--"; //// CHANGED users to Users, userName to full_name ,userEmail to email, userPass to pw_hash (tbh)
			$result2 = $mysql_rpc2->call($query2);
			error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} changed passwords.".PHP_EOL, 3, $LOGFILE);
			$query3 = "--CREATEUSER--UPDATE Users SET pw_reset_flag=NULL WHERE email='$email'--CREATEUSER--";
			$mysql_rpc2->call($query3);
			error_log("[{$date}] [{$file}] [{$level}] Password changed flag set to NULL for User {$_SESSION['user']}.".PHP_EOL, 3, $LOGFILE);
			$msg = "Hello there!\n\nThis is to confirm that your account password was changed.\nIf you made this change then ignore this message.";
			$msg = wordwrap($msg,70);
			$headers = 'From: MooBees' . "\r\n" . 'Reply-To: do-not-reply@moobees.local';
			mail($email,"Account Activity",$msg,$headers);	
			if ($result2) {
				$errTyp = "success";
				$errMSG = "Password has been changed! You are being redirected to the homepage.";
				unset($name);
				unset($email);
				unset($pass);
				header("Refresh: 3; URL=home.php");
				error_log("[{$date}] [{$file}] [{$level}] Email sent to User {$_SESSION['user']} regarding password change.".PHP_EOL, 3, $LOGFILE);
			
			} else {
				$errTyp = "danger";
				$errMSG = "Oops! There was an unknown error, please try later.";
				error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} changed passwords but an email couldn't be sent: Unspecified error from MySQL server.".PHP_EOL, 3, $LOGFILE);
			
			}	
				
		}
		
		
	}
$full_name = $mysql_rpc->call("--GETNAME--SELECT full_name FROM Users WHERE email='" . $_SESSION['user'] . "'--GETNAME--");
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
<nav class="navbar navbar-collapse-top">
      <div class="container">
       
        <div id="navbar" class="navbar-collapse collapse">
          
          <ul class="nav navbar-nav navbar-left">
            
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
			  <span class="glyphicon glyphicon-user"></span>&nbsp;Hi <?php echo $full_name; ?>&nbsp;<span class="caret"></span></a>
             <ul class="dropdown-menu">
				<li><a href="home.php">&nbsp;Home</a></li>
                <li><a href="favorites.php">&nbsp;Favorites</a></li>
				<li><a href="watchlist.php">&nbsp;Watchlist</a></li>
				<li><a href="history.php">&nbsp;Search History</a></li>
				<li><a href="changepwd.php">&nbsp;Change Password</a></li>
				<li><a href="logout.php?logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Sign Out</a></li>
              </ul>
			  
            </li>
          </ul>
        </div>
      </div>
    </nav> 

<div class="container">

	<div id="login-form">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    
    	<div class="col-md-12">
        
        	<div class="form-group">
            	<h2 class="">Change Password.</h2>
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
            	<div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
            	<input type="password" name="pass" class="form-control" placeholder="Enter new Password" maxlength="15" />
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
            	<button type="submit" class="btn btn-block btn-primary" style="background-color:#383838; border:none" name="btn-changepw">Change Password</button>
            </div>
            
            <div class="form-group">
            	<hr />
            </div>
            
        
        </div>
   
    </form>
    </div>	

</div>
<script src="assets/jquery-1.11.3-jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
</body>
</html>
<?php ob_end_flush(); ?>
