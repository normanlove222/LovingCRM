<?php
//this code is used in send_emails.php in the audience section where tags are added and this code gets and returns 
//total count of contacts based on the list id and the tags in the list

require_once('init.php');
header('Content-Type: application/json');

// Selected tags from AJAX request
$selectedLists = isset($_POST['lists']) ? $_POST['lists'] : [];

if (!empty($selectedLists)) {
    // Prepare placeholders for PDO
    $placeholders = implode(',', array_fill(0, count($selectedLists), '?'));

    // Updated query to count contacts with selected tag names
    $sql = "
        SELECT COUNT(DISTINCT c.contact_id) AS count
        FROM contacts c
        JOIN contact_tags ct ON c.contact_id = ct.contact_id
        JOIN tags t ON t.tag_id = ct.tag_id
        WHERE t.name IN ($placeholders)
    ";

    try {
        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);
        $stmt->execute($selectedLists);

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
