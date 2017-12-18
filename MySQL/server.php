<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$LOGFILE = "/var/tmp/mysql.log";
$date = date("Y-m-d h:i:s");
$file = __FILE__;
$level = "Notification";

$connection = new AMQPStreamConnection('192.168.1.101', 5672, 'php', 'ImdbGr0up!');
if($connection){
	error_log("[{$date}] [{$file}] [{$level}] Connection to RabbitMQ established successfully.".PHP_EOL, 3, $LOGFILE);
} else {
	error_log("[{$date}] [{$file}] [{$level}] There was an error while trying to connect to RabbitMQ.".PHP_EOL, 3, $LOGFILE);
}
$channel = $connection->channel();

$channel->queue_declare('mysql_queue', false, false, false, false);
error_log("[{$date}] [{$file}] [{$level}] MySql process started.".PHP_EOL, 3, $LOGFILE);
function LoginQuery($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
    $row = $result->fetch_array();
    $numRows = mysqli_num_rows($result);
    if ($numRows == 1) {
        $dataSet .= '1,' . $row['email'] . ',1,' . $row['pw_reset_flag'];
        return  $dataSet;
        $result->close();
        return $dataSet;
    }else{
        $dataSet .='0,invalid_Credentials,0';
        return  $dataSet;
        $result->close();
        return $dataSet;
    }
		
}

function RetrieveRating($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
    $row = $result->fetch_array();
    $numRows = mysqli_num_rows($result);
    if ($numRows == 1) {
        $dataSet .= '1,' . $row['movie_id'] . ',' . $row['rate'];
		echo $dataSet;
        return  $dataSet;
        $result->close();
        return $dataSet;
    }else{
        $dataSet .='0,movie_does_not_exist,0';
        return  $dataSet;
        $result->close();
        return $dataSet;
    }
		
}



function GetName($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
    $row = $result->fetch_array();
    $full_name = $row['full_name'];
    return $full_name;
}

function CheckUsrExists($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
    $numRows = mysqli_num_rows($result);
    echo $numRows;
    if($numRows == 0) return 'notexists';
    if($numRows !==0) return 'exists';
}

function CreateUser($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
    if ($result) return 1;
    else return 0;
}



function GetMovies($userQuery){
	ini_set("display_errors", true);
	include("account.php");
	$dbh = mysqli_connect($hostname, $username, $password, $project);
		if (!$dbh) {
		    echo "Error: Unable to connect to MySQL." . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			return 	"Debugging error: " . mysqli_connect_error() . PHP_EOL;	    
			exit;
		}

	$dataSet = "";
    $result = $dbh->query($userQuery);
	$numRows = mysqli_num_rows($result);
	if($numRows == 0){
		echo 'nomovies';
		return 'nomovies';
		
	}
    while($row = $result->fetch_array()){
		$rows[] = $row;
	}

	foreach($rows as $row){
		$dataSet .= $row['movie_id'] . ',';
	}
	$dataSet = substr($dataSet,0,-1);
	return $dataSet;
}

echo " [x] Awaiting requests\n";
$callback = function($req) {
	$LOGFILE = "/var/tmp/mysql.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
    $n = (string)$req->body;
	error_log("[{$date}] [{$file}] [{$level}] Request received from {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	echo " [.]Query \"", $n, "\" received.\n";
	if (substr_count($n, "--LOGINQUERY--") == 2) {
        $exp_n = explode("--LOGINQUERY--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(LoginQuery($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
	    echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
		error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }else if (substr_count($n, "--GETRATING--") == 2) {
        $exp_n = explode("--GETRATING--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(RetrieveRating($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
	    echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
        error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }else if (substr_count($n, "--GETNAME--") == 2) {
        $exp_n = explode("--GETNAME--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(GetName($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
	    echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
        error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }else if (substr_count($n, "--CHECKUSREXIST--") == 2) {
        $exp_n = explode("--CHECKUSREXIST--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(CheckUsrExists($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
        echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
        error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }else if (substr_count($n, "--CREATEUSER--") == 2) {
        $exp_n = explode("--CREATEUSER--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(CreateUser($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
	    echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
        error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }else if (substr_count($n, "--GETMOVIE--") == 2) {
        $exp_n = explode("--GETMOVIE--",$n);
        $n = trim($exp_n[1]);
        $msg = new AMQPMessage(GetMovies($n),
                           array('correlation_id' => $req->get('correlation_id'))
                          );
	    echo " [.]Query \"", $n, "\" completed.\n";
	    echo " [x] Awaiting requests\n";

        $req->delivery_info['channel']->basic_publish(
        $msg, '', $req->get('reply_to'));
        error_log("[{$date}] [{$file}] [{$level}] Request completed.  Sending back to {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
	    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']);
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('mysql_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
