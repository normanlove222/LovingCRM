<?php
require_once('init.php');
$ids = isset($_GET['tags']) ? $_GET['tags'] : [];
$list_id = isset($_GET['list_id']) ? $_GET['list_id'] : null;
console_log($ids);
try {

    $count_ids = count($ids);

    if ($count_ids === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'results' => [] // No tags provided, return empty results array
        ]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, $count_ids, '?'));

    $query = "
    SELECT DISTINCT c.contact_id AS id, CONCAT(c.first_name, ' ', c.last_name) AS text, c.state, c.email
    FROM contacts c
    JOIN contact_tags ct ON c.contact_id = ct.contact_id
    WHERE ct.tag_id IN ($placeholders);
    ";

    $stmt = $pdo->prepare($query);

    $stmt->execute(array_merge($ids));

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    console_log($contacts);

    // Convert `id` values to strings
    foreach ($contacts as &$contact) {
        $contact['id'] = (string) $contact['id'];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'results' => $contacts // Select2 expects results to be in an array named "results"
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Database query failed',
        'message' => $e->getMessage()
    ]);
}