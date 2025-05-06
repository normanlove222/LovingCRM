<?php
//when a contact is clicked and contact details display, notes can be edited and saved and this accomplishes that
require_once 'init.php';

header('Content-Type: application/json');

try {
    $contactId = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($contactId === 0) {
        throw new Exception('Invalid contact ID');
    }

    $stmt = $pdo->prepare("UPDATE contacts SET notes = ? WHERE contact_id = ?");
    $stmt->execute([$notes, $contactId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No contact found with the given ID');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Error in update_notes.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
