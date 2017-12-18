<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.1.101', 5672, 'php', 'ImdbGr0up!');
$channel = $connection->channel();

$channel->exchange_declare('update', 'fanout', false, false, false);

list($queue_name, ,) = $channel->queue_declare("updates.prod.frontend", false, false, true, false);

$channel->queue_bind($queue_name, 'update');

echo ' [*] Waiting for updates. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo ' [x] ', $msg->body, "\n";
  if(strpos($msg->body,'UPDATE') !== false) {
	$hostnameArray=explode(',',$msg->body);
	$hostname = $hostnameArray[2];
	if($hostname == 'qafrontend.moobees.local'){
		echo 'An Update is available. Enter password to proceed'."\n";
		$hostnameArray=explode(',',$msg->body);
		#$hostname = $hostnameArray[2];
		$version = $hostnameArray[0];
		$rsyncStr = 'sudo rsync -a -b --backup-dir=/home/lhenriquez/Documents/rabbitmqtest/oldver/v' . $version . ' lhenriquez@'.$hostname.':/home/lhenriquez/Documents/rabbitmqtest/Versions/' . $version .'/moobees/ /var/www/html/moobees';
		#echo $rsyncStr;
		$result = shell_exec($rsyncStr);
		echo $result;
	
	}
	echo ' [*] Waiting for updates. To exit press CTRL+C', "\n";
	}
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
