<?php
require_once('init.php');
header('Content-Type: application/json');

try {
    if (!isset($_GET['tag_id'])) {
        throw new Exception('Tag ID is required');
    }

    $tagId = $_GET['tag_id'];

    // Validate tag_id is numeric
    if (!is_numeric($tagId)) {
        throw new Exception('Invalid tag ID');
    }

    $sql = "
        SELECT c.contact_id, c.first_name, c.last_name, c.email
        FROM contacts c
        JOIN contact_tags ct ON c.contact_id = ct.contact_id
        WHERE ct.tag_id = :tag_id
        ORDER BY c.first_name, c.last_name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tag_id' => $tagId]);

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($contacts);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
