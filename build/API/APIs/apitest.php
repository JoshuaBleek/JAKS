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
        // Check if $flight is not null and if it's an array
        if (is_array($flight) && !$flight['live']['is_ground']) {
            // Check if required keys exist
            if (isset($flight['airline']['name'], $flight['flight']['iata'], $flight['departure']['airport'], $flight['departure']['iata'], $flight['arrival']['airport'], $flight['arrival']['iata'])) {
                $output .= sprintf("%s flight %s from %s (%s) to %s (%s) is in the air." . PHP_EOL,
                    $flight['airline']['name'],
                    $flight['flight']['iata'],
                    $flight['departure']['airport'],
                    $flight['departure']['iata'],
                    $flight['arrival']['airport'],
                    $flight['arrival']['iata']
                );
            }
        }
    }
} else {
    $output = "No flight results found.\n";
}

echo $output;
?>
