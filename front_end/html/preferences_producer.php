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

    // Retrieve all fields from input
    //$name = $input['name'];
    $username = $input['username'];
    $describeYourself = $input['describeYourself'];
    $hobby = $input['hobby'];
    $favMusic = $input['favMusic'];
    $satNight = $input['satNight'];
    $favAnimal = $input['favAnimal'];

    // RabbitMQ connection settings
    $connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    // Declare the queue
    $queue_name = 'preferencesQueue';
    $durable = true;
    $channel->queue_declare($queue_name, false, $durable, false, false);

    // Prepare the message
    $messageBody = json_encode([
        
        'username' => $username,
        'describeYourself' => $describeYourself, 
        'hobby' => $hobby, 
        'favMusic' => $favMusic, 
        'satNight' => $satNight, 
        'favAnimal' => $favAnimal
        ]);
    $msg = new AMQPMessage($messageBody, ['delivery_mode' => 2]);

    // Publish the message
    $channel->basic_publish($msg, '', $queue_name);

    // Return a success message
    echo json_encode(["message" => "Registration data sent for user '{$username}'."]);

    // Close the channel and connection
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>
