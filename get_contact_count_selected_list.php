<?php
//this code is used in send_emails.php in the audience section where tags are added and this code gets and returns total count of contacts based on the list id and the tags in the list

require_once('init.php');
header('Content-Type: application/json');

// Selected tags from AJAX request
$listId = isset($_POST['listId']) ? $_POST['listId'] : [];

if (!empty($listId)) {
    // Prepare placeholders for PDO
    // $placeholders = implode(',', array_fill(0, count($listId), '?'));

    // Updated query to count contacts with selected tag names
    $sql = "
        SELECT COUNT(DISTINCT c.contact_id) AS contact_count
        FROM contacts c
        JOIN (
            SELECT ct.contact_id
            FROM contact_tags ct
            JOIN (
                SELECT l.list_id, jt.tag_id
                FROM lists l
                JOIN JSON_TABLE(l.tags, '$[*]' COLUMNS (tag_id VARCHAR(255) PATH '$')) AS jt
                WHERE l.list_id = ?
            ) AS lt ON ct.tag_id = lt.tag_id
            GROUP BY ct.contact_id
        ) AS distinct_contacts ON c.contact_id = distinct_contacts.contact_id
    ";

    try {
        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$listId]);

        // Fetch the count result
        $count = $stmt->fetchColumn();


        // Return count as JSON
        echo json_encode(['count' => $count]);
    } catch (PDOException $e) {

        echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['count' => 0]);
}

exit;
