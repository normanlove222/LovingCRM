<?php
require_once('init.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['tag_ids']) || !is_array($_POST['tag_ids'])) {
        throw new Exception('No tags selected');
    }

    $tag_ids = array_map('intval', $_POST['tag_ids']);

    $placeholders = str_repeat('?,', count($tag_ids) - 1) . '?';

    // First delete from contact_tags, that tracks which tags each contacts have. 
    $sql = "DELETE FROM contact_tags WHERE tag_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tag_ids);

    // Then delete from tags, which is our main table of all tas and their names
    $sql = "DELETE FROM tags WHERE tag_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tag_ids);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
