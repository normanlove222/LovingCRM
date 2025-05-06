<?php
//what calls this?? function deleteTag(contactId, tagId) inside contacts.php around line 510
require_once('init.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_id = $_POST['contact_id'];
    $tag_id = $_POST['tag_id'];

    try {

        // Delete the tag association
        $stmt = $pdo->prepare("DELETE FROM contact_tags WHERE contact_id = ? AND tag_id = ?");
        $stmt->execute([$contact_id, $tag_id]);

        // Get updated tags list
        $stmt = $pdo->prepare(
            "
            SELECT t.name 
            FROM tags t 
            JOIN contact_tags ct ON t.tag_id = ct.tag_id 
            WHERE ct.contact_id = ? 
            ORDER BY t.created_at DESC
        "
        );
        $stmt->execute([$contact_id]);
        $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['success' => true, 'tags' => $tags]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>