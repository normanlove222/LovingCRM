<?php
require_once 'init.php';
header('Content-Type: application/json'); // Crucial for AJAX

$response = ['error' => null, 'contact' => null, 'tags' => []]; // Initialize structure

// Validate Input
if (!isset($_GET['contact_id']) || !filter_var($_GET['contact_id'], FILTER_VALIDATE_INT) || (int)$_GET['contact_id'] <= 0) {
    $response['error'] = 'Invalid or missing contact ID.';
    // http_response_code(400); // Optional: Set Bad Request status code
    echo json_encode($response);
    exit;
}
$contactId = (int)$_GET['contact_id'];

try {
    // --- Fetch Contact ---
    $stmt_contact = $pdo->prepare("SELECT * FROM contacts WHERE contact_id = :id");
    $stmt_contact->bindParam(':id', $contactId, PDO::PARAM_INT);
    $stmt_contact->execute();
    $contact = $stmt_contact->fetch(PDO::FETCH_ASSOC);

    // Check if contact was found
    if ($contact) {
        $response['contact'] = $contact; // Assign fetched data

        // --- Fetch Tags (only if contact found) ---
        $stmt_tags = $pdo->prepare("
            SELECT t.tag_id, t.name
            FROM tags t
            JOIN contact_tags ct ON t.tag_id = ct.tag_id
            WHERE ct.contact_id = :id
            ORDER BY t.name
        ");
        $stmt_tags->bindParam(':id', $contactId, PDO::PARAM_INT);
        $stmt_tags->execute();
        $tags = $stmt_tags->fetchAll(PDO::FETCH_ASSOC);
        $response['tags'] = $tags; // Assign tags (will be empty array if none found)

    } else {
        // Contact not found - set specific error
        $response['error'] = 'Contact not found.';
        // http_response_code(404); // Optional: Set Not Found status code
    }
} catch (PDOException $e) {
    // Log the detailed error on the server
    error_log("Database Error in ajax_get_contact_details.php for ID $contactId: " . $e->getMessage());
    // Provide a generic error to the client
    $response['error'] = 'A database error occurred while fetching contact details.';
    // http_response_code(500); // Optional: Set Internal Server Error status code
} catch (Exception $e) {
    // Catch other potential errors
    error_log("General Error in ajax_get_contact_details.php for ID $contactId: " . $e->getMessage());
    $response['error'] = 'An unexpected error occurred.';
    // http_response_code(500);
}


// --- Output the JSON Response ---
// Ensure $response is always encoded, even if errors occurred
echo json_encode($response);
exit;
