#!/usr/bin/php
<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

#establishing databse connection with mySQL
$mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}

echo "successfully connected to database".PHP_EOL;

$query = "select * from students;";

$response = $mydb->query($query);
if ($mydb->errno != 0)
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        exit(0);
}

#establishing connection to rabbitMQ


$connection = new AMQPStreamConnection('rabbitmq_host', 5672, 'user', 'password');
$channel = $connection->channel();

#sending a message
$channel->queue_declare('my_queue', false, false, false, false);
$msg = new AMQPMessage('Database and Rabbit have connected courtesy of samih');
$channel->basic_publish($msg, '', 'my_queue');

echo " [x] Sent 'Hello, RabbitMQ!'\n";

$channel->close();
$connection->close();
$mydb->close();
?>
