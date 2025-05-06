<?php
include 'init.php';
//sample code from gpt 4o to get contact email info and send to sendgrid via their API php library. 
//may have  to use composer to install the php library for sendgrid
require 'vendor/autoload.php'; // Composer autoload

// Database query to get contacts and their associated tags
$stmt = $pdo->prepare("
    SELECT c.email, c.first_name, GROUP_CONCAT(t.tag_name SEPARATOR ',') as tags
    FROM contacts c
    LEFT JOIN tags t ON c.user_id = t.user_id
    WHERE c.subscribed = 1
    GROUP BY c.email, c.first_name
");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format contacts for SendGrid
$formattedContacts = [];
foreach ($contacts as $contact) {
    $formattedContacts[] = [
        "email" => $contact['email'],
        "first_name" => $contact['first_name'],
        "tags" => $contact['tags'],  // Contains comma-separated tags like "VIP,newsletter,product_updates"
    ];
}
// Send to SendGrid
$apiKey = 'SG_API_KEY';
$sendgrid = new \SendGrid($apiKey);

// Prepare data for SendGrid API
$data = [
    "contacts" => $formattedContacts
];

// Send to SendGrid (using the code from the previous example)
$response = $sendgrid->client->marketing()->contacts()->put($data);
if ($response->statusCode() == 202) {
    echo "Contacts and tags added successfully!";
} else {
    echo "Error: " . $response->body();
}
