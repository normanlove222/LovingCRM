<?php
require_once('init.php');

try {
    $tags = $_POST['tags'] ?? [];

    if (empty($tags)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    // Get contacts that have ANY of the selected tags, except opt-out
    $placeholders = str_repeat('?,', count($tags) - 1) . '?';
    $sql = "SELECT c.contact_id, c.first_name, c.last_name, c.email, c.state 
            FROM contacts c 
            JOIN contact_tags ct ON c.contact_id = ct.contact_id 
            LEFT JOIN contact_tags ct_opt ON c.contact_id = ct_opt.contact_id 
                AND ct_opt.tag_id = (SELECT tag_id FROM tags WHERE name = 'opt-out')
            WHERE ct.tag_id IN ($placeholders)
            AND ct_opt.tag_id IS NULL
            ORDER BY c.first_name, c.last_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($tags);    

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($contacts);
} catch (PDOException $e) {
    error_log('Error in fetch_contacts: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to fetch contacts']);
}