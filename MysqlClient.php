<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
include("QueryMySQLRPC.php");

$mysql_rpc = new MySqlRPCClient();
$response = $mysql_rpc->call('select * from Users');
echo " [.] Response:<br>", $response, "<br>";

?>
