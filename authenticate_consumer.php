<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Establish a connection to RabbitMQ server with the correct virtual host
$connection = new AMQPStreamConnection('10.244.168.117', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

// Declare the same queue as the producer
$queue_name = 'authenticateQueue';
$durable = true;
$channel->queue_declare($queue_name, false, $durable, false, false);

echo "Waiting for messages in '{$queue_name}'. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo "Received: ", $msg->body, "\n";
};

// Line 24: Consuming messages from the queue
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>
