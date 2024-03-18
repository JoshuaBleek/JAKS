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

// Establish a connection to RabbitMQ server with the correct virtual host
$connection = new AMQPStreamConnection('10.244.168.117', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

// Declare the same queue as the producer
$queue_name = 'paymentQueue';
$durable = true;
$channel->queue_declare($queue_name, false, $durable, false, false);

echo "Waiting for messages in '{$queue_name}'. To exit press CTRL+C\n";

$callback = function ($msg) use ($mydb) {
    echo "Received: ", $msg->body, "\n";
    
    // Decode the message and extract username and password
    $data = json_decode($msg->body, true);
 //   $username = $mydb->real_escape_string($data['username']);
    $name = $mydb->real_escape_string($data['name']);
    $cardNum = $mydb->real_escape_string($data['cardNum']);
    $expiry = $mydb->real_escape_string($data['expiry']);
    $cvv = $mydb->real_escape_string($data['cvv']);
    $address = $mydb->real_escape_string($data['address']);
    $country = $mydb->real_escape_string($data['country']);
    $state = $mydb->real_escape_string($data['state']);
    $town = $mydb->real_escape_string($data['town']);
    $zipcode = $mydb->real_escape_string($data['zipcode']);
    $amount = $mydb->real_escape_string($data['amount']);

    // First, check if the username and password already exist
//    $checkQuery = "SELECT * FROM user WHERE username = '$username'";
 //   $result = $mydb->query($checkQuery);
    
    // Insert Query
//    if ($result->num_rows > 0) {
        // if user does  exist process the payment

        $insertQuery = "INSERT INTO userPayment (name, cardNum, expiry, cvv, address, country, state, town, zipcode, amount) 
        VALUES ( '$name', '$cardNum', '$expiry', '$cvv', '$address', '$country', '$state', '$town', '$zipcode', '$amount')";

        if ($mydb->query($insertQuery) == TRUE) {
            echo "payment processed successfully" . PHP_EOL;
        } else {
            echo "Error: " . $mydb->error . PHP_EOL;
        }
    
   // } else {
        // If the user does not exist, reject payment process
    //    echo "Username does not exist. Please try again" . PHP_EOL;
   // }
};


// Line 24: Consuming messages from the queue
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>
