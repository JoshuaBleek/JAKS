<?php
require_once "error_handler.php";  // Ensure this inclusion is necessary or remove if not

// Custom error handler function
define('DEBUG', true);  // Set to true for development, false for production


function customErrorHandler($severity, $message, $file, $line) {
    // Ignore error if it should not be reported
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    // Format the error data
    $errorData = [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ];

    // Convert error data to JSON
    $jsonData = json_encode($errorData);
    if ($jsonData === false) {
        error_log('JSON encode error: ' . json_last_error_msg());
        return; // Optionally skip sending corrupt data
    }

    // Send the error data to Fluentd
    sendLogToFluentd($jsonData);
    
    // Execute PHP internal error handler as well
    return false; // Ensure standard PHP error handler continues
}

// Function to send logs to Fluentd
function sendLogToFluentd($data) {
    $ch = curl_init('http://localhost:9880/');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
    }
    curl_close($ch);
}

// Set the custom error handler
set_error_handler("customErrorHandler");

// For testing: trigger an error, make sure to handle or remove this in production
if (defined('DEBUG') && DEBUG) {
    trigger_error("Test error", E_USER_ERROR);
}
?>
