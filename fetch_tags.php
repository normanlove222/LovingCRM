<?php
require_once('init.php');

try {
    // Get all tags ordered by name
    $stmt = $pdo->query("SELECT tag_id, name FROM tags ORDER BY name");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Format tags for Select2
    $formattedTags = array_map(function ($tag) {
        return [
            'id' => $tag['tag_id'],
            'text' => $tag['name']
        ];
    }, $tags);

    header('Content-Type: application/json');
    echo json_encode($formattedTags);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to fetch tags']);
}