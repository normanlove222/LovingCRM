<?php
require_once('init.php');

$contact_id = $_GET['contact_id'] ?? '';

if ($contact_id) {
    $query = "SELECT c.*, GROUP_CONCAT(t.name) as tags 
              FROM contacts c 
              LEFT JOIN contact_tags ct ON c.contact_id = ct.contact_id 
              LEFT JOIN tags t ON ct.tag_id = t.tag_id 
              WHERE c.contact_id = ?
              GROUP BY c.contact_id";

    $params = [$contact_id];
    $contact = getContactFromQuery($query, $params);

    if ($contact) {
        $contact['tags'] = $contact['tags'] ? explode(',', $contact['tags']) : [];
        echo json_encode($contact);
    } else {
        echo json_encode(['error' => 'Contact not found']);
    }
} else {
    echo json_encode(['error' => 'No contact ID provided']);
}
