<?php
require_once 'vendor/autoload.php';

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
$queue_name = 'loginQueue';
$durable = true;
$channel->queue_declare($queue_name, false, $durable, false, false);

echo "Waiting for messages in '{$queue_name}'. To exit press CTRL+C\n";

$callback = function ($msg) use ($mydb) {
    echo "Received: ", $msg->body, "\n";
    
    // Decode the message and extract username and password
    $data = json_decode($msg->body, true);
    $username = $mydb->real_escape_string($data['username']);
    $password = $mydb->real_escape_string($data['password']); 
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    // First, check if the username and password already exist
    $checkQuery = "SELECT * FROM user WHERE username = '$username' AND password = '$hashedPassword'";
    $result = $mydb->query($checkQuery);
    
    // Insert Query
    if ($result->num_rows > 0) {
        // If the user exists, login
        echo "Login Successful" . PHP_EOL;
        $insertQuery = "INSERT INTO userLogin (username, password, success, error_message) VALUES ('$username', '$hashedPassword', TRUE, NULL)";
        
        if ($mydb->query($insertQuery) === TRUE) {
            echo "New record created successfully" . PHP_EOL;
        } else {
            echo "Error: " . $mydb->error . PHP_EOL;
        }
    } else {
        // If the user does not exist, No username and password
        echo "Username and Password do not exist" . PHP_EOL;
        $insertQuery = "INSERT INTO userLogin (success, error_message) VALUES (FALSE, $mydb->error)";

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
