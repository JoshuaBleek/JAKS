#!/usr/bin/php
<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$mydb = new mysqli('10.244.213.77', 'dataMan', 'JAKS', 'JAKSdb');
if ($mydb->errno != 0) {
    echo "Failed to connect to database: " . $mydb->error . PHP_EOL;
    exit(0);
}
echo "Successfully connected to database" . PHP_EOL;

$connection = new AMQPStreamConnection('10.244.168.117', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

$channel->queue_declare('loginQueue', false, true, false, false);

echo "Waiting for messages in 'loginQueue'. To exit press CTRL+C\n";

$callback = function ($msg) use ($mydb, $channel) {
    $data = json_decode($msg->body, true);
    $username = $mydb->real_escape_string($data['username']);
    $password = $data['password']; 

    // Here is the authentication logic
    $query = "SELECT password FROM user WHERE username = '$username'";
    $result = $mydb->query($query);

    $isLoginSuccessful = false;
    $sessionToken = '';

    if ($result->num_rows > 0) {
        $userRow = $result->fetch_assoc();
        if (password_verify($password, $userRow['password'])) {
            $isLoginSuccessful = true;
            // Generate a new session token
            $sessionToken = bin2hex(random_bytes(32)); // for example
            // Store or update the session token in the database as needed
        }
    }

    $responseArray = $isLoginSuccessful ? 
                     ["message" => "Login successful","session_token" => $sessionToken] :
                     ["message" => "Login failed", "error" => "Invalid credentials"];
    
    $responseMsg = new AMQPMessage(
        json_encode($responseArray),
        ['correlation_id' => $msg->get('correlation_id')]
    );

    $channel->basic_publish($responseMsg, '', $msg->get('reply_to'));
};

$channel->basic_consume('loginQueue', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>