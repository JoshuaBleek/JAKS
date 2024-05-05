#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Database Connection
$mydb = new mysqli('10.244.213.77', 'dataMan', 'JAKS', 'JAKSdb');
if ($mydb->errno != 0) {
    echo "Failed to connect to database: " . $mydb->error . PHP_EOL;
    exit(0);
}
echo "Successfully connected to database" . PHP_EOL;

// RabbitMQ Connection
$connection = new AMQPStreamConnection('10.244.168.117', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

$queue_name = 'testQueue';
$durable = true;
$channel->queue_declare($queue_name, false, $durable, false, false);

$callback = function ($msg) use ($mydb) {
    echo "Received: ", $msg->body, "\n";
    
    // Decode the message and extract username and password
    $data = json_decode($msg->body, true);
    $username = $mydb->real_escape_string($data['username']);
    $password = $mydb->real_escape_string($data['password']); // Consider hashing the password

    // Insert Query
    $insertQuery = "INSERT INTO user (username, password) VALUES ('$username', '$password')";
    $selectQuery =  "SELECT * FROM user;";
    if ($mydb->query($insertQuery) === TRUE) {
        echo "New record created successfully" . PHP_EOL;
    } else {
        echo "Error: " . $insertQuery . "\n" . $mydb->error . PHP_EOL;
    }
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>