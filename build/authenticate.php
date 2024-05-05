<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Enable error reporting for debugging
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// Function to publish messages to RabbitMQ
function publishToQueue($userData) {
    $host = '10.244.168.117'; // Change this to your RabbitMQ server address
    $port = 5672; // Default port for RabbitMQ
    $user = 'test'; // Change this to your RabbitMQ user
    $pass = 'test'; // Change this to your RabbitMQ password
    $queue = 'testQueue';

    try {
        $connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $msg = new AMQPMessage(json_encode($userData), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();

        return true;
    } catch (\Exception $e) {
        // Catch and log any exceptions
        error_log($e->getMessage());
        return false;
    }
}

$response = 'An error occurred';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $request = $_POST;

    switch ($request["type"]) {
        case "register":
            if (isset($request['username']) && isset($request['password']) && isset($request['email'])) {
                // Publish user data to RabbitMQ
                $result = publishToQueue([
                    'type'     => 'register',
                    'username' => $request['username'],
                    'password' => $request['password'],
                    'email'    => $request['email']
                ]);

                if ($result) {
                    $response = "User data sent for registration";
                } else {
                    $response = "Failed to send user data for registration";
                }
            } else {
                $response = "Username, password, or email not provided";
            }
            break;
        default:
            $response = "Unsupported request type";
            break;
    }
} else {
    $response = "Invalid request method or missing type";
}

header('Content-Type: application/json');
echo json_encode(['response' => $response]);
exit(0);
?>
