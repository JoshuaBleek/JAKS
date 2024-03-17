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
    $inputJSON = file_get_contents('php:> ');
    $input = json_decode($inputJSON, true);

    // Retrieve all fields fro> 
    $username = ['username'];
    $name = ['name'];
    $cardNum =['cardNum'];
    $expiry = ['expiry'];
    $cvv = ['cvv'];
    $address = ['address'];
    $country = ['country'];
    $state = ['state'];
    $town = ['town'];
    $zipcode = ['zipcode'];
    $amount = ['amount'];


    // RabbitMQ connection settings
    $connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    // Declare the queue
    $queue_name = 'paymentQueue';
    $durable = true;
    $channel->queue_declare($queue_name, false, $durable, false, false);

    // Prepare the message
    $messageBody = json_encode([
        'name' => $name, 
        'username' => $username, 
        'cardNum' =>  $cardNum,
        'expiry' => $expiry,
        'cvv' => $cvv,
        'address' => $address,
        'country' => $country,
        'state' => $state,
        'town' =>  $town,
        'zipcode' => $zipcode,  
        'amount' => $amount
    ]);
    $msg = new AMQPMessage($messageBody, ['delivery_mode' => 2]);

    // Publish the message
    $channel->basic_publish($msg, '', $queue_name);

    // Return a success message
    echo json_encode(["message" => "Payment data sent for user '{$username}'."]);

    // Close the channel and connection
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>
