<?php
require_once 'vendor/autoload.php';
require_once 'composer.json';


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Establish a connection to RabbitMQ server
$connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test');
$channel = $connection->channel();

// Declare a queue
$queue_name = 'testQueue';
$durable = true; // the queue will survive server restarts

$channel->queue_declare($queue_name, false, $durable, false, false);

echo "Queue '{$queue_name}' declared.\n";

// Close the channel and the connection
$channel->close();
$connection->close();
