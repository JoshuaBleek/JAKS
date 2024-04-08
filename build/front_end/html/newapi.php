<?php
// Function to log to the console
function console_log($message) {
    echo "<script>console.log(" . json_encode($message) . ");</script>";
}

// Sanitize and validate input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Your API Key
$apiKey = 'sh428739766321522266746152871799';

// Capture GET parameters
$origin = isset($_GET['origin']) ? sanitize_input($_GET['origin']) : '';
$destination = isset($_GET['destination']) ? sanitize_input($_GET['destination']) : '';
$departureDate = isset($_GET['departureDate']) ? sanitize_input($_GET['departureDate']) : '';

// Initiate cURL session
$ch = curl_init();

// Prepare the request body for the /search/create endpoint
$requestBody = json_encode([
    "query" => [
        "market" => "US",
        "locale" => "en-US",
        "currency" => "USD",
        "queryLegs" => [
            [
                "origin" => $origin,
                "destination" => $destination,
                "departureDate" => $departureDate,
            ],
        ],
        "cabinClass" => "CABIN_CLASS_ECONOMY",
        "adults" => 1,
    ]
]);

// Set cURL options for /search/create endpoint
curl_setopt($ch, CURLOPT_URL, 'https://partners.api.skyscanner.net/apiservices/v3/flights/live/search/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'x-api-key: ' . $apiKey,
    'Content-Type: application/json'
));

// Execute cURL session for /create
$responseCreate = curl_exec($ch);

// Log the entire response for debugging
console_log($responseCreate);

// Check for cURL errors
if (curl_errno($ch)) {
    console_log('Curl error: ' . curl_error($ch));
    exit;
}

$responseCreateData = json_decode($responseCreate, true);

if (isset($responseCreateData['error'])) {
    console_log('API error: ' . $responseCreateData['error']);
    exit;
}

$sessionToken = $responseCreateData['sessionToken'] ?? '';

if (empty($sessionToken)) {
    console_log('Error: Session Token not received.');
    exit;
}

// Set cURL options for /search/poll endpoint using the sessionToken
curl_setopt($ch, CURLOPT_URL, 'https://partners.api.skyscanner.net/apiservices/v3/flights/live/search/poll/' . $sessionToken);
curl_setopt($ch, CURLOPT_HTTPGET, true);

// Execute cURL session for /poll
$responsePoll = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    console_log('Curl error on poll: ' . curl_error($ch));
    curl_close($ch);
    exit;
}

// Close cURL session
curl_close($ch);

$flightData = json_decode($responsePoll, true);

if (empty($flightData) || !isset($flightData['content']['results'])) {
    console_log('Error: No flight data received.');
    exit;
}

$formattedData = ''; // Initialize variable to store formatted data

foreach ($flightData['content']['results'] as $flight) {
    // Format each flight as needed for the frontend
    $formattedData .= "<div class='flight-card'>";
    // Add details from the $flight array
    $formattedData .= "</div>";
}

// Output the formatted data
echo $formattedData;
?>
