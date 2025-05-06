<?php
require_once 'init.php';

header('Content-Type: application/json');

try {
    $contactId = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;

    if ($contactId === 0) {
        throw new Exception('Invalid contact ID');
    }

    $fields = [
        'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'company'
    ];

    $updates = [];
    $params = [];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
        }
    }

    if (empty($updates)) {
        throw new Exception('No fields to update');
    }

    $params[] = $contactId;

    $sql = "UPDATE contacts SET " . implode(', ', $updates) . " WHERE contact_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No changes were made to the contact');
    }

    // Fetch the updated contact details
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE contact_id = ?");
    $stmt->execute([$contactId]);
    $updatedContact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$updatedContact) {
        throw new Exception('Failed to retrieve updated contact details');
    }

    echo json_encode(['success' => true, 'contact' => $updatedContact]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
