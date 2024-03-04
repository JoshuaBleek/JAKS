<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Establish a connection to RabbitMQ server
$connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test');
$channel = $connection->channel();

// Declare the queue in case it doesn't exist
$queue_name = 'testQueue';
$durable = true; // the queue will survive server restarts
$channel->queue_declare($queue_name, false, $durable, false, false);

// Create a message
$messageBody = 'Hello, RabbitMQ!';
$msg = new AMQPMessage($messageBody, array('delivery_mode' => 2)); // Persistent message

// Publish the message to the queue
$channel->basic_publish($msg, '', $queue_name);

echo "I'm bidirectional '{$queue_name}'.\n";

// Close the channel and the connection
$channel->close();
$connection->close();
