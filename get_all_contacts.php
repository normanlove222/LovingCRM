<?php
require_once('init.php');

try {
    $stmt = $pdo->query("
    SELECT contact_id, first_name, last_name, email, state 
    FROM contacts 
    ORDER BY first_name");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($contacts);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error']);
}