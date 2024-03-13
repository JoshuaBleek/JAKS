<?php

header('Access-Control-Allow-Origin: *'); // Allows all origins
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Adjust methods as needed
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Include any other headers you need

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    $username = $input['username'];
    $password = $input['password']; // Consider hashing the password

    $connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    $queue_name = 'loginQueue';
    $durable = true;
    $channel->queue_declare($queue_name, false, $durable, false, false);

    $messageBody = json_encode(array('username' => $username, 'password' => $password));
    $msg = new AMQPMessage($messageBody, array('delivery_mode' => 2));

    $channel->basic_publish($msg, '', $queue_name);

    echo json_encode(["message" => "Login data sent for user '{$username}'."]); // Return a success message

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]); // Return an error message
}
?>
