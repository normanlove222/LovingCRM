<?php
require_once 'functions.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

$list_id = isset($_GET['list_id']) ? $_GET['list_id'] : null;

try {
    if ($list_id) {
        $stmt = $pdo->prepare("SELECT tags FROM lists WHERE list_id = ?");
        $stmt->execute([$list_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['tags'])) {
            // Log the retrieved tags
            error_log('Results are : ' . print_r($result, true));

            // Convert the JSON string to an array of integers
            $tagIds = json_decode($result['tags'], true);

            // Check if $tagIds is an array and not empty
            if (is_array($tagIds) && !empty($tagIds)) {
                // Fetch tag names for these IDs
                $placeholders = str_repeat('?,', count($tagIds) - 1) . '?';
                $tagStmt = $pdo->prepare("SELECT tag_id AS id, name AS text FROM tags WHERE tag_id IN ($placeholders)");
                $tagStmt->execute($tagIds);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                // Log the contents of $tags
                error_log('Retrieved tag names: ' . print_r($tags, true));
            } else {
                $tags = [];
                error_log('No valid tag IDs found.');
            }

            header('Content-Type: application/json');
            echo json_encode(['results' => $tags]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['results' => []]);
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}