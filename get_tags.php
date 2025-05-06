<?php
require_once('init.php');

try {
    $stmt = $pdo->prepare("SELECT tag_id, name FROM tags ORDER BY name");
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($tags);
} catch (PDOException $e) {
    error_log("Error in get_tags.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch tags']);
}
