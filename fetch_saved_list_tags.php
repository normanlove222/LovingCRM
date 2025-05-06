<?php
require_once('init.php');

try {
    // Assume list_id is passed as a GET parameter
    $list_id = $_GET['list_id'] ?? null;

    if (!$list_id) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'List ID is required']);
        exit;
    }

    // Fetch the tags for the given list_id
    $stmt = $pdo->prepare("SELECT tags FROM lists WHERE list_id = ?");
    $stmt->execute([$list_id]);
    $list = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$list) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'List not found']);
        exit;
    }

    // Decode the tags JSON
    $tagIds = json_decode($list['tags'], true);

    if (empty($tagIds)) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    // Prepare placeholders for the IN clause
    $placeholders = str_repeat('?,', count($tagIds) - 1) . '?';

    // Fetch tag names from the tags table
    $sql = "SELECT tag_id, name FROM tags WHERE tag_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tagIds);
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
    error_log('Error in fetch_saved_list_tags: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to fetch tags']);
}
