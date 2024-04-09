<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    $password = $input['password'];

    $connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    list($callback_queue) = $channel->queue_declare("", false, false, true, false);

    $corr_id = uniqid();
    $response = null;
    $callback = function ($msg) use ($corr_id, &$response) {
        if ($msg->get('correlation_id') == $corr_id) {
            $response = $msg->body;
        }
    };

    $channel->basic_consume($callback_queue, '', false, true, false, false, $callback);

    $messageBody = json_encode(['username' => $username, 'password' => $password]);
    $msg = new AMQPMessage(
        $messageBody,
        ['correlation_id' => $corr_id, 'reply_to' => $callback_queue]
    );

    $channel->basic_publish($msg, '', 'loginQueue');

    while (!$response) {
        $channel->wait();
    }

    echo $response;

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>
