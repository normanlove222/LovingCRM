<?php
// get_list_tags.php
require_once('init.php');

$list_id = isset($_GET['list_id']) ? $_GET['list_id'] : null;

try {
    $stmt = $pdo->prepare("SELECT tags FROM lists WHERE list_id = ?");
    $stmt->execute([$list_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($result['tags']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch list tags']);
}
