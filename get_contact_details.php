<?php
require_once('init.php');

header('Content-Type: application/json');

try {
    $contactId = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
    // error_log("Requested Contact ID: " . $contactId);

    if ($contactId === 0) {
        throw new Exception('Invalid contact ID');
    }

    $contact = getContactDetails($contactId);
    // error_log("Contact data: " . print_r($contact, true));

    if (!$contact) {
        throw new Exception('Contact not found');
    }

    echo json_encode($contact);
} catch (Exception $e) {
    error_log("Error in get_contact_details.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function getContactDetails($contactId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "SELECT c.*, GROUP_CONCAT(t.name) as tags 
                               FROM contacts c 
                               LEFT JOIN contact_tags ct ON c.contact_id = ct.contact_id 
                               LEFT JOIN tags t ON ct.tag_id = t.tag_id 
                               WHERE c.contact_id = :contact_id 
                               GROUP BY c.contact_id"
        );
        $stmt->execute(['contact_id' => $contactId]);
        // error_log("SQL Query: " . $stmt->queryString);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contact) {
            $contact['tags'] = $contact['tags'] ? explode(',', $contact['tags']) : [];
        }
        
        return $contact;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Database error occurred");
    }
}
