<?php
//this gets and returns to ajax call the number of contacts with this given tag.
require_once('init.php');


if (isset($_GET['tag_id'])) {
    $tag_id = (int)$_GET['tag_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_tags WHERE tag_id = :tag_id");
    $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    echo $count;
}
