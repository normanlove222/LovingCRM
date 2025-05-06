<?php
require_once 'init.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['list_name']) || !isset($_POST['tags'])) {
        throw new Exception('Missing required data');
    }

    $list_name = trim($_POST['list_name']);
    $tags = $_POST['tags'];

    // Convert tags array to JSON
    $tags_json = json_encode($tags);

    // Insert into lists table
    $stmt = $pdo->prepare("INSERT INTO lists (list_name, tags) VALUES (:list_name, :tags)");
    $result = $stmt->execute([
        ':list_name' => $list_name,
        ':tags' => $tags_json
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to save list');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
