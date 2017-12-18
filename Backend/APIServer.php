<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$LOGFILE = "/var/tmp/backend.log";
$date = date("Y-m-d h:i:s");
$file = __FILE__;
$level = "Notification";
error_log("[{$date}] [{$file}] [{$level}] Back-end process started.".PHP_EOL, 3, $LOGFILE);

$connection = new AMQPStreamConnection('192.168.1.101', 5672, 'php', 'ImdbGr0up!');
if($connection){
	error_log("[{$date}] [{$file}] [{$level}] Connection to RabbitMQ established successfully.".PHP_EOL, 3, $LOGFILE);
} else {
	error_log("[{$date}] [{$file}] [{$level}] There was an error while trying to connect to RabbitMQ.".PHP_EOL, 3, $LOGFILE);
}

$channel = $connection->channel();

$channel->queue_declare('API_queue', false, false, false, false);

function movieQuery($searchQuery){
	$LOGFILE = "/var/tmp/backend.log";
	$date = date("Y-m-d h:i:s");
	$file = __FILE__;
	$level = "Notification";
	$fp = fopen($searchQuery, "r");
	while(!$fp){
		$fp = fopen($searchQuery, "r");
	}
	error_log("[{$date}] [{$file}] [{$level}] URL received: {$searchQuery}.".PHP_EOL, 3, $LOGFILE);
	$contents = "";
	while($more = fread($fp,1000)){
		$contents .= $more;
  	}
	if($contents){
		error_log("[{$date}] [{$file}] [{$level}] Query to API was successful.".PHP_EOL, 3, $LOGFILE);
	
	} else {
		error_log("[{$date}] [{$file}] [{$level}] There was an error while trying to query the API.".PHP_EOL, 3, $LOGFILE);
	}
	return $contents;

}


echo " [x] Awaiting requests\n";
$callback = function($req){
				$LOGFILE = "/var/tmp/backend.log";
				$date = date("Y-m-d h:i:s");
				$file = __FILE__;
				$level = "Notification";
				$n = (string)$req->body;
				echo " [.]Query \"", $n, "\" received.\n";
				$msg = new AMQPMessage(movieQuery($n),array('correlation_id' => $req->get('correlation_id')));
				echo " [.]Query \"", $n, "\" completed.\n";
				echo " [x] Awaiting requests\n";
				error_log("[{$date}] [{$file}] [{$level}] Sending result to queue {$req->get('reply_to')}.".PHP_EOL, 3, $LOGFILE);
			    $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));
			    $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);
			};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('API_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
