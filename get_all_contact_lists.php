<?php
// get_all_contact_lists.php
require_once 'init.php'; // Ensure this includes your DB connection ($pdo)

header('Content-Type: application/json');

try {
    // Fetch list_id, list_name, and created_at from the lists_of_contacts table
    $stmt = $pdo->query("SELECT list_id, list_name, created_at
                         FROM lists_of_contacts
                         ORDER BY list_name ASC"); // Or ORDER BY created_at DESC

    $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($lists); // Output the results as JSON

} catch (PDOException $e) {
    error_log("Database Error in get_all_contact_lists.php: " . $e->getMessage());
    // Return a JSON error object
    echo json_encode(['error' => 'Failed to retrieve lists from database.']);
    // Optionally return an empty array: echo json_encode([]);
    exit; // Stop script execution on error
}
