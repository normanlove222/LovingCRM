<?php
require_once('init.php');
header('Content-Type: application/json');

try {
    // Query all tags from the tags table
    $sql = "SELECT tag_id, name FROM tags ORDER BY tag_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optionally, add an 'other_info' field if you want to display more info per tag
    foreach ($tags as &$tag) {
        $tag['other_info'] = isset($tag['other_info']) ? $tag['other_info'] : '';
    }

    echo json_encode($tags);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
