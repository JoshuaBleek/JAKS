<?php
$queryString = http_build_query([
    'access_key' => '8ee27a0316da3d7817305d9c2afa7a77'
]);

$ch = curl_init(sprintf('%s?%s', 'http://api.aviationstack.com/v1/flights', $queryString));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$json = curl_exec($ch);
curl_close($ch);

$api_result = json_decode($json, true);

$output = "";

if (isset($api_result['data']) && is_array($api_result['data'])) {
    foreach ($api_result['data'] as $flight) {
        // Checks...
        $output .= "<div class='flight-card'>";
        $output .= "<h2>" . htmlspecialchars($flight['airline']['name']) . " " . htmlspecialchars($flight['flight']['iata']) . "</h2>";
        $output .= "<p>Departure: " . htmlspecialchars($flight['departure']['airport']) . " (" . htmlspecialchars($flight['departure']['iata']) . ")</p>";
        $output .= "<p>Arrival: " . htmlspecialchars($flight['arrival']['airport']) . " (" . htmlspecialchars($flight['arrival']['iata']) . ")</p>";
        $output .= "<button class='book-button'>Book Now</button>";
        $output .= "</div>";
    }
} else {
    $output = "<p>No flight results found.</p>";
}

echo $output;
?>
