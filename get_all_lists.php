<?php
require_once('init.php');

try {
    $stmt = $pdo->query("SELECT
    l.list_id,
    l.list_name,
    GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS tag_names
FROM
    lists l
JOIN
    json_table(l.tags, '$[*]' COLUMNS (tag_id INT PATH '$')) AS jt
JOIN
    tags t ON jt.tag_id = t.tag_id
GROUP BY
    l.list_id, l.list_name
ORDER BY
    l.created_at DESC;
");
    $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the entire array to see its structure
    // error_log(print_r($lists, true));

    // Check if the array is not empty and the first element contains the 'tag_names' key
    if (!empty($lists) && isset($lists[0]['tag_names'])) {
        error_log($lists[0]['tag_names']);
    } else {
        error_log('tag_names key not found or array is empty');
    }
    header('Content-Type: application/json');
    echo json_encode($lists);
} catch (Exception $e) {
    // Handle exception
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching the lists.']);
}