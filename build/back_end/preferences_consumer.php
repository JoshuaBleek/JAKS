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
$queue_name = 'authenticateQueue';
$durable = true;
$channel->queue_declare($queue_name, false, $durable, false, false);

echo "Waiting for messages in '{$queue_name}'. To exit press CTRL+C\n";

$callback = function ($msg) use ($mydb) {
    echo "Received: ", $msg->body, "\n";
    
    // Decode the message and extract username and password
    $data = json_decode($msg->body, true);
    $username = $mydb->real_escape_string($data['username']);
    //$name = $mydb->real_escape_string($data['name']);
    $describeYourself = $mydb->real_escape_string($data['describeYourself']);
    $hobby = $mydb->real_escape_string($data['hobby']); 
    $favMusic = $mydb->real_escape_string($data['favMusic']); 
    $satNight = $mydb->real_escape_string($data['satNight']); 
    $favAnimal = $mydb->real_escape_string($data['favAnimal']); 


    // // First, check if the username and password already exist
    // $checkQuery = "SELECT * FROM user WHERE username = '$username' ";
    // $result = $mydb->query($checkQuery);
    
    // // Insert Query
    // if ($result->num_rows > 0) {
    //     // If the user exists, send a message or handle as needed
    //     echo "Username and password already exist." . PHP_EOL;
    // } else {
        // If the user does not exist, proceed to insert the new record


    // process and calculate the rating number    
    // Key: A = -3
    //      H = -1
    //      M = +1
    //      S = +2
    $ratingNum = 1;
//    $ratingLocation="";   
    foreach(array($describeYourself, $hobby, $favMusic,  $satNight, $favAnimal) as $x){
            if ($x[0] == 'A' ){
                $ratingNum + 2;
            }else if ($x[0] == 'H' ){
                $ratingNum + 1;
            }if ($x[0] == 'M' ){
                $ratingNum;
            } else{
                $ratingNum -1;
            }
        }

        $insertQuery = "INSERT INTO user (username, describeYourself, hobby, favMusic, satNight, favAnimal, ratingNum) 
        VALUES ('$username', '$describeYourself', '$hobby', '$favMusic', '$satNight', '$favAnimal', '$ratingNum')";

        if ($mydb->query($insertQuery) == TRUE) {
            echo "Preferences oriented successfully" . PHP_EOL;
        } else {
            echo "Error: " . $mydb->error . PHP_EOL;
        }
};


// Line 24: Consuming messages from the queue
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>
