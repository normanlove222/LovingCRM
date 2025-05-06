<?php
require_once('init.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_id = $_POST['contact_id'];
    $tag_name = $_POST['tag'];

    try {

        // First check if tag exists
        $stmt = $pdo->prepare("SELECT tag_id FROM tags WHERE name = ?");
        $stmt->execute([$tag_name]);
        $existingTag = $stmt->fetch();

        // If tag doesn't exist, create it
        if (!$existingTag) {
            $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
            $stmt->execute([$tag_name]);
            $tag_id = $pdo->lastInsertId();
        } else {
            $tag_id = $existingTag['tag_id'];
        }

        // Add tag to contact if not already associated
        $stmt = $pdo->prepare("INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (?, ?)");
        $stmt->execute([$contact_id, $tag_id]);

        // Get all tags for this contact
        $stmt = $pdo->prepare("
            SELECT t.name 
            FROM tags t 
            JOIN contact_tags ct ON t.tag_id = ct.tag_id 
            WHERE ct.contact_id = ? 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$contact_id]);
        $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['success' => true, 'tags' => $tags]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>