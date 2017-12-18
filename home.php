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
		error_log("[{$date}] [{$file}] [{$level}] User from IP {$_SERVER['REMOTE_ADDR']} tried to access the homepage.".PHP_EOL, 3, $LOGFILE);
		header("Location: index.php");
		exit;
	}
	$full_name = $mysql_rpc->call("--GETNAME--SELECT full_name FROM Users WHERE email='" . $_SESSION['user'] . "'--GETNAME--");
	error_log("[{$date}] [{$file}] [{$level}] Homepage loaded by {$_SESSION['user']}.".PHP_EOL, 3, $LOGFILE);

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
		  <br><br>
	  		<input type="search" id="search-box" name="search-box" placeholder="Search for Titles, Casting..." required />
			<br><br>
			<input type="radio" name="choice" value="title" id="title" CHECKED/>
			<label for="title">&nbsp;Title&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
			<input type="radio" name="choice" value="person" id="person"  />
			<label for="actor">&nbsp;Person&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
			<input type="radio" name="choice" value="year" id="year"  />
			<label for="byyear">&nbsp;Year </label>
		  <br> <br>
		 <?php
			#This php code will grab the necessary information from the radio buttons and the user and construct the search query
			$userInput = $_GET["search-box"];
			$filteredInput = urlencode($userInput);
			#---------Begin History------------
			$isHistory = $mysql_rpc->call("--CHECKUSREXIST--SELECT * FROM Search_History WHERE email='" . $_SESSION['user'] . "' AND movie_id='" . $filteredInput . "'--CHECKUSREXIST--");
			if($isHistory=='notexists' && $filteredInput !== ""){
				$emailAddr = $_SESSION['user'];
				error_log("[{$date}] [{$file}] [{$level}] Adding search string '{$filteredInput}' to {$_SESSION['user']}'s search history.".PHP_EOL, 3, $LOGFILE);
				$query2 = "--CREATEUSER--INSERT INTO Search_History(email,movie_id) VALUES('$emailAddr','$filteredInput')--CREATEUSER--";
				$result2 = $mysql_rpc->call($query2);
			}else if($isHistory=='exists' && $filteredInput !== ""){
				$mysql_rpc2 = new MySqlRPCClient();
				error_log("[{$date}] [{$file}] [{$level}] Search string '{$filteredInput}' already in {$_SESSION['user']}'s search history; updating timestamp.".PHP_EOL, 3, $LOGFILE);
				$emailAddr = $_SESSION['user'];
				$rmvHistory = "--CREATEUSER--DELETE FROM Search_History WHERE email='" . $emailAddr . "' AND movie_id='" . $filteredInput ."'--CREATEUSER--";
				$result2 = $mysql_rpc2->call($rmvHistory);
				$emailAddr = $_SESSION['user'];
				$query2 = "--CREATEUSER--INSERT INTO Search_History(email,movie_id) VALUES('$emailAddr','$filteredInput')--CREATEUSER--";
				$result2 = $mysql_rpc2->call($query2);
			
			}
			#---------End History------------
			#Determines what radio button is selected and creates the proper query string for it.
			#Pass $send to rabbitmq because it is the variable that contains the query string
			$send = "";
			$baseURL= "http://image.tmdb.org/t/p/w185";
			
			$userChoice = $_GET["choice"];
			if($userChoice == "title") {
				$send.='https://api.themoviedb.org/3/search/movie?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&query=' . $filteredInput . '&page=1&include_adult=false';
				error_log("[{$date}] [{$file}] [{$level}] Sending search criteria to back-end server. Title search: {$filteredInput}.".PHP_EOL, 3, $LOGFILE);
				$response = $rpc_query->call($send);
				if($response !== ""){
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
				} else {
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
				}
				$parsed_json = json_decode($response, true);
				if($parsed_json['total_results']==0){
					echo '<h3>No Results.</h3>';
					exit;
				}
				$baseURL= "http://image.tmdb.org/t/p/w185";
				foreach ($parsed_json['results'] as $key => $value) {
					echo "<div style='float: left; padding-right: 50px; clear: both;'>";
					if($parsed_json['results'][$key]['poster_path']==null){
						$poster = 'noimg.png';
					} else {
						$poster = $baseURL.$parsed_json['results'][$key]['poster_path'];
					}
					$titleStr = "<h4><br><br>" . $parsed_json['results'][$key]['title'] . "</h4>";
					echo '<a href="moviedetails.php?movieID=' . $parsed_json['results'][$key]['id'] . '">' . $titleStr . '</a>';
					echo "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>" . '<br><br>';
					echo "<h4>Overview</h4>" . $parsed_json['results'][$key]['overview'] . '<br>';
					echo "<br>Release date: " . $parsed_json['results'][$key]['release_date'] . '<br>';
					#echo $parsed_json['results']['rating'] . '<br>';

					echo '<br><hr><br>';
					echo '</div>';

					}
				echo '<br><hr><br><br><br><br><br><br><br><br><br>';
			}
			
			if($userChoice == "person") {
				$send.='https://api.themoviedb.org/3/search/person?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&query=' . $filteredInput . '&page=1&include_adult=false';
				error_log("[{$date}] [{$file}] [{$level}] Sending search criteria to back-end server. Person search: {$filteredInput}.".PHP_EOL, 3, $LOGFILE);
				$response = $rpc_query->call($send);
				if($response !== ""){
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
				} else {
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
				}
				$parsed_json = json_decode($response, true);
				if($parsed_json['total_results']==0){
					echo '<h3>No Results.</h3>';
					exit;
				}
				foreach ($parsed_json['results'] as $key => $value) {	
					echo "<div style='float: left; padding-right: 50px; clear: both;'>";
					if($parsed_json['results'][$key]['profile_path']==null){
						$poster = 'noimg.png';
					} else {
						$poster = $baseURL.$parsed_json['results'][$key]['profile_path'];
					}
					$nameStr = "<h4><br><br>" . $parsed_json['results'][$key]['name'] . "</h4>";
					echo '<a href="persondetails.php?personID=' . $parsed_json['results'][$key]['id'] . '">' . $nameStr . '</a>';
					#echo "<h4><br><br>" . $parsed_json['results'][$key]['name'] . "</h4>";
					echo "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>";
					echo "<div style='float: left; padding-right: 50px;'>";
					echo "<h4>Known For: </h4>";
					foreach($parsed_json['results'][$key]['known_for'] as $key2 => $value2){
						$knownForStr = $parsed_json['results'][$key]['known_for'][$key2]['title'] . '<br>';
						echo '<a href="moviedetails.php?movieID=' . $parsed_json['results'][$key]['known_for'][$key2]['id'] . '">' . $knownForStr . '</a>';

					}
					#echo "<br>Release date: " . $parsed_json['results'][$key]['release_date'] . '<br>';
					#echo $parsed_json['results']['rating'] . '<br>';
					echo '</div>';
					echo '<br><hr><br>';
					echo '</div>';

					}
				echo '<br><hr><br><br><br><br><br><br><br><br><br>';
			}
			
			if($userChoice == "year") {
				if(!(is_nan($filteredInput)) && $filteredInput>1000 && $filteredInput<2100){
					$send.='https://api.themoviedb.org/3/discover/movie?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&sort_by=release_date.asc&include_adult=false&include_video=false&page=1&primary_release_year=' . $filteredInput;
					error_log("[{$date}] [{$file}] [{$level}] Sending search criteria to back-end server. Year search: {$filteredInput}.".PHP_EOL, 3, $LOGFILE);
					$response = $rpc_query->call($send);
					if($response !== ""){
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
					} else {
						error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
					}
					$parsed_json = json_decode($response, true);
					if($parsed_json['total_results']==0){
						echo '<h3>No Results.</h3>';
						exit;
					}
					$baseURL= "http://image.tmdb.org/t/p/w185";
					foreach ($parsed_json['results'] as $key => $value) {
						echo "<div style='float: left; padding-right: 50px; clear: both;'>";
						if($parsed_json['results'][$key]['poster_path']==null){
							$poster = 'noimg.png';
						} else {
							$poster = $baseURL.$parsed_json['results'][$key]['poster_path'];
						}
						$titleStr = "<h4><br><br>" . $parsed_json['results'][$key]['title'] . "</h4>";
						echo '<a href="moviedetails.php?movieID=' . $parsed_json['results'][$key]['id'] . '">' . $titleStr . '</a>';
						echo "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>" . '<br><br>';
						echo "<h4>Overview</h4>" . $parsed_json['results'][$key]['overview'] . '<br>';
						echo "<br>Release date: " . $parsed_json['results'][$key]['release_date'] . '<br>';
						#echo $parsed_json['results']['rating'] . '<br>';

						echo '<br><hr><br>';
						echo '</div>';

						}
					echo '<br><hr><br><br><br><br><br><br><br><br><br>';
				} else {
					echo "<h3>That doesn't look like a valid year :'(</h3>";
				
				}
			
			}
			
			#-------NOW PLAYING--------
			
			echo '</div></div><p></p><hr><h4>Now Playing</h4><hr><br>';
			$nowPlaying =  'https://api.themoviedb.org/3/movie/now_playing?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&page=1';
			error_log("[{$date}] [{$file}] [{$level}] Sending query to back-end: Get movies NOW PLAYING.".PHP_EOL, 3, $LOGFILE);
			$responseNowPlaying = $rpc_query->call($nowPlaying);
			if($responseNowPlaying !== ""){
				error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
				} else {
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
			}
			$parsed_jsonNowPlaying = json_decode($responseNowPlaying, true);
			$baseURLNowPlaying= "http://image.tmdb.org/t/p/w185";
			foreach ($parsed_jsonNowPlaying['results'] as $key => $value) {
				if($parsed_jsonNowPlaying['results'][$key]['poster_path']==null){
					$poster = 'noimg.png';
				} else {
					$poster = $baseURL.$parsed_jsonNowPlaying['results'][$key]['poster_path'];
				}
				echo "<div style='float: left'>";
				echo '<a href="moviedetails.php?movieID=' . $parsed_jsonNowPlaying['results'][$key]['id'] . '">' . '<img style="width:205px; height:278px; padding-right: 20px" title= "' . $parsed_jsonNowPlaying['results'][$key]['title'] . '"' . ' src="' . $poster . '" alt="Poster">' . '</a>';
				#echo '<p>' . '<a href="moviedetails.php?movieID=' . $parsed_jsonNowPlaying['results'][$key]['id'] . '">' . $parsed_jsonNowPlaying['results'][$key]['title']. '</a>' . '</p></div>';
				echo '</p></div>';

			}
			
			
			
			
			#Code Below is used to handle genre selection and construct the genre search query
			#$genreSend contains the information that is needed to send to the back end server for the query 
			$genreChoice = $_GET["genre"];
			$genreSend = "with_genres=";
			if(isset($genreChoice) && $genreChoice!="0") {
			echo "A non zero value was chosen";
			$genreSend.=$genreChoice;
			echo $genreSend;
			
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