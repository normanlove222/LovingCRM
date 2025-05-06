<?php
require_once 'init.php';

header('Content-Type: application/json');

try {
    if (empty($_POST['name'])) {
        throw new Exception('Tag name is required');
    }

    $tagName = trim($_POST['name']);

    $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
    $stmt->execute([$tagName]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}