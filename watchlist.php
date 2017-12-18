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
        
		<form class="clearfix searchform">
		  <label for="search-box">
			<span class="fa fa-search fa-flip-horizontal fa-2x"></span>
		  </label>
		 <?php
			$wlMovies = $mysql_rpc->call("--GETMOVIE--SELECT movie_id FROM Watchlist_by_User WHERE email='" . $_SESSION['user'] . "'--GETMOVIE--");
			error_log("[{$date}] [{$file}] [{$level}] Getting Watchlist for User {$_SESSION['user']}.".PHP_EOL, 3, $LOGFILE);
			if($wlMovies=='nomovies'){
				echo '<h4>You have nothing in your Watchlist.</h4>';
				echo '<br><br>You are being redirected to the home page...';
				header("Refresh: 3; URL=home.php");
				exit;
			}
			$wlMoviesArray = explode(",",$wlMovies);
			foreach($wlMoviesArray as $movieID){
				$send = "";
				$baseURL= "http://image.tmdb.org/t/p/w185";
				$send.='https://api.themoviedb.org/3/movie/' . $movieID . '?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US';
				$response = $rpc_query->call($send);
				$parsed_json = json_decode($response, true);
				echo "<div style='float: left; padding-right: 50px; clear: both;'>";
				if($parsed_json['poster_path']==null){
					$poster = 'noimg.png';
				} else {
					$poster = $baseURL.$parsed_json['poster_path'];
				}
				$titleStr = "<h4><br><br>" . $parsed_json['title'] . "</h4>";
				echo '<a href="moviedetails.php?movieID=' . $movieID . '">' . $titleStr . '</a>';
				echo '<a href="moviedetails.php?movieID=' . $parsed_json['id'] . '">' . "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>" . '</a>' . '<br><br>';
				echo "<h4>Overview</h4>" . $parsed_json['overview'] . '<br>';
				echo "<br>Release date: " . $parsed_json['release_date'] . '<br>';
				#echo $parsed_json['results']['rating'] . '<br>';
				echo '<br><hr><br>';
				echo '</div>';
						
			
			}
			
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