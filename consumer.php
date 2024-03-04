<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Establish a connection to RabbitMQ server
$connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test');
$channel = $connection->channel();

// Declare the same queue as the producer to ensure they are both using the same one
$queue_name = 'testQueue';
$channel->queue_declare($queue_name, false, true, false, false);

echo "Waiting for messages in '{$queue_name}'. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo "Received: ", $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>
