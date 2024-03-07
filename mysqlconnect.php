#!/usr/bin/php
<?php
require_once ('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('amqplib');
require_once('testRabbitMQ.ini');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//                      DATABASE
//establishing databse connection with mySQL
$mydb = new mysqli('127.0.0.1','dataMan','JAKS','JAKSdb');
if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit(0);
}
echo "successfully connected to database".PHP_EOL;


//                      RABBIT MQ
//establishing connection to rabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, $USER, $PASSWORD, $VHOST);
$channel = $connection->channel();
// Declare the queue in case it doesn't exist
$channel->queue_declare($QUEUE);


//assigns and executes showing the databse initially
$query = "select * from User;";
$response = $mydb->query($query);
if ($mydb->errno != 0)
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        exit(0);
}



$channel->basic_consume($QUEUE, '', false, true, false, false, $callback);


?>
