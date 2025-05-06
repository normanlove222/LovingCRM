<?php
require_once('init.php');
    if (isset($_GET['contact_id'])) {
        try {
            error_log(print_r($_GET['contact_id'], true));
            $stmt = $pdo->prepare("
               SELECT t.tag_id, t.name
               FROM tags t
               JOIN contact_tags ct ON t.tag_id = ct.tag_id
               WHERE ct.contact_id = ?
           ");
            $stmt->execute([$_GET['contact_id']]);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log(print_r($tags, true));
            header('Content-Type: application/json');
            echo json_encode($tags);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Contact ID is required']);
    }
?>