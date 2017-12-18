<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.1.101', 5672, 'php', 'ImdbGr0up!');
$channel = $connection->channel();

$channel->exchange_declare('update', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "CHECK";
$msg = new AMQPMessage($data.','.getHostName());

$channel->basic_publish($msg, 'update');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>

