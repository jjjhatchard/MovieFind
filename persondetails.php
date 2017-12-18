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
			
	<?php
			#This php code will grab the necessary information from the radio buttons and the user and construct the search query
			
			$personID = $_GET['personID'];
			error_log("[{$date}] [{$file}] [{$level}] Loading details for person ID {$personID}.".PHP_EOL, 3, $LOGFILE);
			#Determines what radio button is selected and creates the proper query string for it.
			#Pass $send to rabbitmq because it is the variable that contains the query string
			$send = "";
			$baseURL= "http://image.tmdb.org/t/p/w185";
			error_log("[{$date}] [{$file}] [{$level}] Sending query to back-end for person ID {$personID}.".PHP_EOL, 3, $LOGFILE);
			$send.='https://api.themoviedb.org/3/person/' . $personID . '?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US';
			$response = $rpc_query->call($send);
			if($response !== ""){
				error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
				} else {
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
			}
			$parsed_json = json_decode($response, true);
			#$send2.='https://api.themoviedb.org/3/movie/' . $movieID . '/credits?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US';
			#$response2 = $rpc_query->call($send2);
			#$parsed_json2 = json_decode($response2, true);
			echo "<div style='float: left; padding-right: 50px; clear: both;'>";
			if($parsed_json['profile_path']==null){
				$poster = 'noimg.png';
			} else {
				$poster = $baseURL.$parsed_json['profile_path'];
			}
			echo "<h4><br><br>" . $parsed_json['name'] . "</h4>";
			echo "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>" . '<br><br>';
			echo "<h4>Biography</h4>" . $parsed_json['biography'] . '<br><br><br>';
			$send2 = 'https://api.themoviedb.org/3/search/person?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&query=' . urlencode($parsed_json['name']) . '&page=1&include_adult=false';
			$response2 = $rpc_query->call($send2);
			$parsed_json2 = json_decode($response2, true);
			echo '</div><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
				
			echo "<h4>Known For: </h4>";
			foreach ($parsed_json2['results'] as $key => $value) {	
				foreach($parsed_json2['results'][$key]['known_for'] as $key2 => $value2){
					$knownForStr = $parsed_json2['results'][$key]['known_for'][$key2]['title'] . '<br>';
					echo '<a href="moviedetails.php?movieID=' . $parsed_json2['results'][$key]['known_for'][$key2]['id'] . '">' . $knownForStr . '</a>';
					
				}
				echo '<br><hr><br>';
				echo '</div>';
				
				}
			echo '<br><hr><br><br><br><br><br><br><br><br><br>';
			echo '</div>';
			echo '<br><hr><br>';
	?>
        
		  
    <script src="assets/jquery-1.11.3-jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
</body>
</html>
<?php ob_end_flush(); ?>