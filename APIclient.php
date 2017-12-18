<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
include("QueryBackendAPI.php");

$rpc_query = new QueryBackendAPI();
$response = $rpc_query->call(urlencode('This is the end'));
echo "Response:<br>";
$parsed_json = json_decode($response, true);
foreach ($parsed_json as $key => $value) {
    echo $key.' - '.count($value) . '<br>';
}

echo '<br><br>List of results: <br><br>';
foreach ($parsed_json['results'] as $key => $value) {	
	foreach($parsed_json['results'][$key] as $key2 => $value2){
		echo $key2 . ' - ' . $value2;
		echo '<br>';	
		
	}    

echo '<br><br>';
}


?>
