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
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Moo-Bees, <?php echo $full_name; ?></title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

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
<?php		
		#Rating system
		$movieID = $_GET['movieID'];
		$isRated = $mysql_rpc->call("--CHECKUSREXIST--SELECT * FROM Rated_Movies WHERE email='" . $_SESSION['user'] . "' AND movie_id='" . $movieID . "'--CHECKUSREXIST--");
		if ($isRated=='notexists'){
			error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} has not been rated by user {$_SESSION['user']}.".PHP_EOL, 3, $LOGFILE);
			echo '<div class="stars">';
			echo '<form method="POST" action="">';
			echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
			echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
			echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4"/>';
			echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
			echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
			echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
			echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
			echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
			echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
			echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			echo '</form>';
			echo '</div>';
		}
		
		if ($isRated=='exists'){
			$email = $_SESSION['user'];
			$responseRating = $mysql_rpc->call("--GETRATING--SELECT email, movie_id, rate FROM Rated_Movies WHERE email='$email' AND movie_id='$movieID'--GETRATING--");
			$sqlData = explode(",",$responseRating);
			$rateVal = $sqlData[2];
			echo '<div class="stars">';
			echo '<form method="POST" action="">';
			if($rateVal=='5'){
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5" CHECKED/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4"/>';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			} else if ($rateVal==4){
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4" CHECKED/>';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			} else if ($rateVal==3){
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4"/>';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3" CHECKED/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			} else if ($rateVal==2){
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4"/>';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2" CHECKED/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			} else if ($rateVal==1){
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4" />';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1" CHECKED/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			} else {
				echo '<input onchange="this.form.submit();" class="star star-5" id="star-5" type="radio" name="star" value="5"/>';
				echo '<label class="star star-5" for="star-5" title="Certified Awesome Sauce :)"></label>';
				echo '<input onchange="this.form.submit();" class="star star-4" id="star-4" type="radio" name="star" value="4"/>';
				echo '<label class="star star-4" for="star-4" title="Cool stuff!"></label>';
				echo '<input onchange="this.form.submit();" class="star star-3" id="star-3" type="radio" name="star" value="3"/>';
				echo '<label class="star star-3" for="star-3" title="It\'s ight"></label>';
				echo '<input onchange="this.form.submit();" class="star star-2" id="star-2" type="radio" name="star" value="2"/>';
				echo '<label class="star star-2" for="star-2" title="Meh..."></label>';
				echo '<input onchange="this.form.submit();" class="star star-1" id="star-1" type="radio" name="star" value="1"/>';
				echo '<label class="star star-1" for="star-1" title="Geez, I can\'t even..."></label>';
			}
			
			echo '</form>';
			echo '</div>';
		}
		$usrEmail = $_SESSION['user'];
		if( isset($_POST['star']) ) {
			$rate = $_POST['star'];
			$mysql_rpc2 = new MySqlRPCClient();
			$chkMovieStr = "--CHECKUSREXIST--SELECT * FROM Movies WHERE id='" . $movieID . "'--CHECKUSREXIST--";
			$chkMovie = $mysql_rpc2->call($chkMovieStr);
			if ($chkMovie=='notexists'){
				error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} rating Movie ID {$movieID} with value of {$rate}.".PHP_EOL, 3, $LOGFILE);
				$query2 = "--CREATEUSER--INSERT INTO Movies(id,title,poster_path,overview,full_json) VALUES('$movieID','$movieTitle','$moviePoster','$movieOverview','$movieFullJSON')--CREATEUSER--";
				$result2 = $mysql_rpc2->call($query2);
				$query3 = "--CREATEUSER--INSERT INTO Rated_Movies(email,movie_id,rate) VALUES('$usrEmail','$movieID',$rate)--CREATEUSER--";
				$result3 = $mysql_rpc2->call($query3);
				header("Refresh:0");
			} else if ($chkMovie=='exists'){
				$chkMovieStr = "--CHECKUSREXIST--SELECT * FROM Rated_Movies WHERE movie_id='" . $movieID . "' AND email='$usrEmail'--CHECKUSREXIST--";
				$chkMovie = $mysql_rpc2->call($chkMovieStr);
				if ($chkMovie=='exists'){
					$usrEmail = $_SESSION['user'];
					error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} updating the rating for Movie ID {$movieID} with value of {$rate}.".PHP_EOL, 3, $LOGFILE);
					$query3 = "--CREATEUSER--UPDATE Rated_Movies SET rate='$rate' WHERE email='$usrEmail' AND movie_id='$movieID'--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					header("Refresh:0");
				} else if($chkMovie=='notexists'){
					$usrEmail = $_SESSION['user'];
					error_log("[{$date}] [{$file}] [{$level}] User {$_SESSION['user']} rating existing Movie ID {$movieID} with value of {$rate}.".PHP_EOL, 3, $LOGFILE);
					$query3 = "--CREATEUSER--INSERT INTO Rated_Movies(email,movie_id,rate) VALUES('$usrEmail','$movieID',$rate)--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					header("Refresh:0");				
				
				}
				
					
			}
				
			}
	  
?>
        <div class="row">
        <div class="col-lg-12">
			
			
			
	<?php
			#This php code will grab the necessary information from the radio buttons and the user and construct the search query
			
			$movieID = $_GET['movieID'];
			error_log("[{$date}] [{$file}] [{$level}] Loading details for movie ID {$movieID}.".PHP_EOL, 3, $LOGFILE);
			
			$isWatchList = $mysql_rpc->call("--CHECKUSREXIST--SELECT * FROM Watchlist_by_User WHERE email='" . $_SESSION['user'] . "' AND movie_id='" . $movieID . "'--CHECKUSREXIST--");
			$isFavorite = $mysql_rpc->call("--CHECKUSREXIST--SELECT * FROM Fav_and_Recommended WHERE email='" . $_SESSION['user'] . "' AND movie_id='" . $movieID . "'--CHECKUSREXIST--");
			$send = "";
			$baseURL= "http://image.tmdb.org/t/p/w185";
			$send.='https://api.themoviedb.org/3/movie/' . $movieID . '?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US';
			error_log("[{$date}] [{$file}] [{$level}] Sending query to back-end for movie ID {$movieID}.".PHP_EOL, 3, $LOGFILE);
			
			$response = $rpc_query->call($send);
			if($response !== ""){
				error_log("[{$date}] [{$file}] [{$level}] Response from back-end: OK!".PHP_EOL, 3, $LOGFILE);
				} else {
					error_log("[{$date}] [{$file}] [{$level}] Response from back-end: UNKNOWN".PHP_EOL, 3, $LOGFILE);
			}
			$parsed_json = json_decode($response, true);
			$send2.='https://api.themoviedb.org/3/movie/' . $movieID . '/credits?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US';
			$response2 = $rpc_query->call($send2);
			$parsed_json2 = json_decode($response2, true);
			echo "<div style='float: left; padding-right: 50px; clear: both;'>";
			if ($isWatchList=='notexists'){
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} does not exist in {$_SESSION['user']}'s watchlist.".PHP_EOL, 3, $LOGFILE);
				echo "<form method='POST' action=''>";
				echo '<button type="submit" class="btn btn-block btn-primary" style="height:30px; width:180px; background-color:#383838; border:none" name="btn-addWL">Add to Watchlist</button>';
				echo '</form>';
			
			}
			
			if ($isWatchList=='exists'){
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} already exists in {$_SESSION['user']}'s watchlist.".PHP_EOL, 3, $LOGFILE);
				echo "<form method='POST' action=''>";
				echo '<button type="submit" class="btn btn-block btn-primary" style="height:30px; width:180px; background-color:#383838; border:none" name="btn-rmvWL">Remove From Watchlist</button>';
				echo '</form>';
			}
			echo '<br><br>';
			#------------
			if ($isFavorite=='notexists'){
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} does not exist in {$_SESSION['user']}'s favorites.".PHP_EOL, 3, $LOGFILE);
				echo "<form method='POST' action=''>";
				echo '<button type="submit" class="btn btn-block btn-primary" style="height:30px; width:180px; background-color:#383838; border:none" name="btn-addFav">Add to Favorites</button>';
				echo '</form>';
			
			}
			
			if ($isFavorite=='exists'){
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} already exists in {$_SESSION['user']}'s favorites.".PHP_EOL, 3, $LOGFILE);
				echo "<form method='POST' action=''>";
				echo '<button type="submit" class="btn btn-block btn-primary" style="height:30px; width:180px; background-color:#383838; border:none" name="btn-rmvFav">Remove From Favorites</button>';
				echo '</form>';
			}		
			#-------------
			if($parsed_json['poster_path']==null){
				$poster = 'noimg.png';
			} else {
				$poster = $baseURL.$parsed_json['poster_path'];
			}
			echo "<h4><br><br>" . $parsed_json['title'] . "</h4>";
			echo "<img style='float: left; padding-right: 50px; clear: both;' src='$poster'/>" . '<br><br>' . '</div>';
			echo "<h4>Overview</h4>" . $parsed_json['overview'] . '<br>';
			echo '<h4>Release date: </h4>' . $parsed_json['release_date'];
			echo "<h4>Original Language: " . '</h4>' . $parsed_json['original_language'];
			echo "<h4>Vote Average: ". '</h4>' . $parsed_json['vote_average'] ;
			echo '</div>';
			echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><h4>Cast:</h4>";
			foreach($parsed_json2['cast'] as $key => $value){
				echo '<i>"' . $parsed_json2['cast'][$key]['character'] . '"</i>, played by <b>';
				$perName = $parsed_json2['cast'][$key]['name'] .'</b><br>';
				echo '<a href="persondetails.php?personID=' . $parsed_json2['cast'][$key]['id'] . '">' . $perName . '</a>';
			}
			echo '<br><hr><br>';
			$movieID = $parsed_json['id'];
			$movieTitle = addslashes($parsed_json['title']);
			$moviePoster = addslashes($parsed_json['poster_path']);
			$movieOverview = addslashes($parsed_json['overview']);
			$movieFullJSON = addslashes($response);
			$usrEmail = $_SESSION['user'];		
			if( isset($_POST['btn-addWL']) ) {
				$mysql_rpc2 = new MySqlRPCClient();
				$chkMovieStr = "--CHECKUSREXIST--SELECT * FROM Movies WHERE id='" . $movieID . "'--CHECKUSREXIST--";
				$chkMovie = $mysql_rpc2->call($chkMovieStr);
				if ($chkMovie=='notexists'){
					$query2 = "--CREATEUSER--INSERT INTO Movies(id,title,poster_path,overview,full_json) VALUES('$movieID','$movieTitle','$moviePoster','$movieOverview','$movieFullJSON')--CREATEUSER--";
					$result2 = $mysql_rpc2->call($query2);
					$query3 = "--CREATEUSER--INSERT INTO Watchlist_by_User(email,movie_id) VALUES('$usrEmail','$movieID')--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					header("Refresh:0");
				} else if ($chkMovie=='exists'){
					$usrEmail = $_SESSION['user'];
					$query3 = "--CREATEUSER--INSERT INTO Watchlist_by_User(email,movie_id) VALUES('$usrEmail','$movieID')--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					header("Refresh:0");
				}
				
				
			}
			if( isset($_POST['btn-rmvWL']) ) {
				$mysql_rpc2 = new MySqlRPCClient();
				$rmvMovieStr = "--CREATEUSER--DELETE FROM Watchlist_by_User WHERE email='" . $usrEmail . "' AND movie_id='" . $movieID ."'--CREATEUSER--";
				$result2 = $mysql_rpc2->call($rmvMovieStr);
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} removed from {$_SESSION['user']}'s watchlist.".PHP_EOL, 3, $LOGFILE);
				
				header("Refresh:0");
				
			}
			
			#-----------------------
			if( isset($_POST['btn-addFav']) ) {
				$mysql_rpc2 = new MySqlRPCClient();
				$chkMovieStr = "--CHECKUSREXIST--SELECT * FROM Movies WHERE id='" . $movieID . "'--CHECKUSREXIST--";
				$chkMovie = $mysql_rpc2->call($chkMovieStr);
				if ($chkMovie=='notexists'){
					$query2 = "--CREATEUSER--INSERT INTO Movies(id,title,poster_path,overview,full_json) VALUES('$movieID','$movieTitle','$moviePoster','$movieOverview','$movieFullJSON')--CREATEUSER--";
					$result2 = $mysql_rpc2->call($query2);
					$query3 = "--CREATEUSER--INSERT INTO Fav_and_Recommended(email,movie_id) VALUES('$usrEmail','$movieID')--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} added to master movie table and {$_SESSION['user']}'s favorites.".PHP_EOL, 3, $LOGFILE);
					header("Refresh:0");
				} else if ($chkMovie=='exists'){
					$usrEmail = $_SESSION['user'];
					$query3 = "--CREATEUSER--INSERT INTO Fav_and_Recommended(email,movie_id) VALUES('$usrEmail','$movieID')--CREATEUSER--";
					$result3 = $mysql_rpc2->call($query3);
					error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} added to {$_SESSION['user']}'s favorites.".PHP_EOL, 3, $LOGFILE);
					header("Refresh:0");
				}
				
				
			}
			if( isset($_POST['btn-rmvFav']) ) {
				$mysql_rpc2 = new MySqlRPCClient();
				$rmvMovieStr = "--CREATEUSER--DELETE FROM Fav_and_Recommended WHERE email='" . $usrEmail . "' AND movie_id='" . $movieID ."'--CREATEUSER--";
				$result2 = $mysql_rpc2->call($rmvMovieStr);
				error_log("[{$date}] [{$file}] [{$level}] Movie ID {$movieID} removed from {$_SESSION['user']}'s favorites.".PHP_EOL, 3, $LOGFILE);
				header("Refresh:0");
				
			}
			
			#----------------SIMILAR TO THIS-----
			echo '<p></p><hr><h4>Similar to ' . $parsed_json['title'] . '</h4><hr><br>';
			$nowPlaying =  'https://api.themoviedb.org/3/movie/' .  $movieID .'/similar?api_key=6125b0dedc32edf620522dd340cb7fd7&language=en-US&page=1';
			$responseNowPlaying = $rpc_query->call($nowPlaying);
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
				echo '<p></p></div>';

			}
				
	?>
        
		  
    <script src="assets/jquery-1.11.3-jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
</body>
</html>
<?php ob_end_flush(); ?>