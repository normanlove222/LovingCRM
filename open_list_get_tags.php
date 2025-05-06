<?php
//may not be needed any more
require_once 'functions.php';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

try {
    if (!empty($ids)) {
        $idArray = array_filter(explode(',', $ids), 'is_numeric'); // Ensure IDs are numeric
        if (!empty($idArray)) {
            $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
            $stmt = $pdo->prepare("SELECT tag_id, name as text FROM tags WHERE tag_id IN ($placeholders)");
            $stmt->execute($idArray);
        } else {
            $stmt = $pdo->prepare("SELECT tag_id, name as text FROM tags WHERE 0"); // No valid IDs
            $stmt->execute();
        }
    } else if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT tag_id, name as text FROM tags WHERE name LIKE ?");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT tag_id, name as text FROM tags");
        $stmt->execute();
    }
    
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['results' => $tags]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>