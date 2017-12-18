<?php 
ob_start();
	session_start();
	require_once __DIR__ . '/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;
	include("QueryMySQLRPC.php");
	include("QueryBackendAPI.php");
	$mysql_rpc = new MySqlRPCClient();
	$rpc_query = new QueryBackendAPI();
	$LOGFILE = "/var/tmp/frontend.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");
		exit;
	}
	$full_name = $mysql_rpc->call("--GETNAME--SELECT full_name FROM Users WHERE email='" . $_SESSION['user'] . "'--GETNAME--");

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Moo-Bees, <?php echo $full_name; ?></title>
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
	<div id="wrapper">
	<div class="container">
    
    	<div class="page-header">
    	
		<br>
    	</div>
        
        <div class="row">
        <div class="col-lg-12">
        <form method='POST' action=''>
			<button type="submit" class="btn btn-block btn-primary" style="height:30px; width:180px; background-color:#383838; border:none" name="btn-clrHistory">Clear History</button>
			</form>
			<br>
			
		<form class="clearfix searchform">
		  <label for="search-box">
			<span class="fa fa-search fa-flip-horizontal fa-2x"></span>
		  </label>
		 <?php
			
			if( isset($_POST['btn-clrHistory']) ) {
				$mysql_rpc2 = new MySqlRPCClient();
				$usrEmail = $_SESSION['user'];
				$clrHistoryStr = "--CREATEUSER--DELETE FROM Search_History WHERE email='" . $usrEmail . "'--CREATEUSER--";
				$result2 = $mysql_rpc2->call($clrHistoryStr);
				error_log("[{$date}] [{$file}] [{$level}] Clearing search history for User {$_SESSION['user']}.".PHP_EOL, 3, $LOGFILE);
				header("Refresh: 0");
				
			}
			error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} has accessed her search history.".PHP_EOL, 3, $LOGFILE);
			$histStrs = $mysql_rpc->call("--GETMOVIE--SELECT movie_id FROM Search_History WHERE email='" . $_SESSION['user'] . "' order by timestamp desc--GETMOVIE--");
			if($histStrs=='nomovies'){
				echo '<h4>Nothing in your search history.</h4>';
				echo '<br><br>You are being redirected to the home page...';
				header("Refresh: 3; URL=home.php");
				exit;
			}
			$histStrsArray = explode(",",$histStrs);
			$searchOrder = 0;
			echo '<table class="table">';
			echo '<tr>';
			echo '<th>Index</th>';
			echo '<th>String</th>';
			echo ' </tr>';
			foreach($histStrsArray as $searchStr){
				$searchOrder += 1;
				echo '<tr>';
				echo '<td>' . $searchOrder . '</td>';
				echo '<td>' . urldecode($searchStr) . '</td>';
				echo '</tr>';
			
			}
			echo '</table>';
			
			
			
	     ?>
		</form>
        </div>
        </div>
    
    </div>
    
    </div>
    
    <script src="assets/jquery-1.11.3-jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
</body>
</html>
<?php ob_end_flush(); ?>