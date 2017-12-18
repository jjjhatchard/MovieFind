<?php

	/*

	ob_start();

	session_start();

	if( isset($_SESSION['user'])!="" ){

		header("Location: home.php");

	}

	require_once __DIR__ . '/vendor/autoload.php';

	use PhpAmqpLib\Connection\AMQPStreamConnection;

	use PhpAmqpLib\Message\AMQPMessage;

	include("QueryMySQLRPC.php");



	$mysql_rpc = new MySqlRPCClient();

	$error = false;

	*/

?>

<!DOCTYPE html>

<html>



<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Moo-Bees!</title>

<h1>Password Reset</h1>

<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />

<link rel="stylesheet" href="style.css" type="text/css" />

</head>



<header>

	<div id="header">

</header>



<body>

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

            	<input type="password" name="pass1" id="pass1" class="form-control" placeholder="Your Password" maxlength="15" />

                </div>

                <span class="text-danger"><?php echo $passError; ?></span>

        </div>

	<div class="form-group"> 

            	<div class="input-group">

                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>

            	<input type="password" name="pass2" id="pass2" class="form-control" placeholder="Confirm Your Password" maxlength="15"
			onChange="checkPasswordMatch();" />

                </div>
		<div class="registrationFormAlert" id="divCheckPasswordMatch"></div>

                <span class="text-danger"><?php echo $passError; ?></span>

            </div>
		
	<div class="form-group">

            <button type="submit" class="btn btn-block btn-primary" style="background-color:#383838; border:none" name="btn-signup">Submit</button>

        </div>

	<div class="form-group">

            	<a href="index.php">Back to sign in...</a>

        </div>

</body>

</html>

<script>
function checkPasswordMatch() {
    var password = $("pass1").val();
    var confirmPassword = $("pass2").val();

    if (password != confirmPassword)
        $("divCheckPasswordMatch").html("Passwords do not match!");
    else
        $("divCheckPasswordMatch").html("Passwords match.");
}

$(document).ready(function () {
   $("pass2").keyup(checkPasswordMatch);
});
</script>