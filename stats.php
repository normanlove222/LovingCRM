<?php
//stats that can be received from sendGrid to see how many sends opens, spam reports etc, per day
//helpful when working on stats

include 'init.php';
require 'vendor/autoload.php';
$sg = new \SendGrid(SG_API_KEY);

$query_params = json_decode('{"aggregated_by": "day", "limit": 1, "start_date": "2024-10-01", "end_date": "2024-11-01", "offset": 1}');

try {
    $response = $sg->client->stats()->get(null, $query_params);

    echo "<h3>Status Code:</h3>";
    echo "<pre>" . $response->statusCode() . "</pre>";

    echo "<h3>Headers:</h3>";
    echo "<pre>" . print_r($response->headers(), true) . "</pre>";

    echo "<h3>Response Body:</h3>";
    echo "<pre>" . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . "</pre>";
} catch (Exception $e) {
    echo '<pre>Caught exception: ' . $e->getMessage() . '</pre>';
}
?>

<style>
    pre {
        background: #f4f4f4;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
        overflow-x: auto;
    }

    h3 {
        color: #333;
        margin-top: 20px;
    }
</style>