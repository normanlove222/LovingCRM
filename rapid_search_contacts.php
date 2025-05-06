<?php
session_start();  
require_once('c1.php');  

require_once 'functions.php';
require_once('require_login.php');

if (isset($_GET['term'])) {
    $searchTerm = $_GET['term'];

    try {
        // Add wildcard characters for prefix matching
        $searchTerm = "%$searchTerm%";

        // Modify the query to concatenate first_name and last_name
        $query = "
            SELECT * 
            FROM contacts 
            WHERE 
                CONCAT(first_name, ' ', last_name) LIKE ? 
                OR first_name like ?
                OR last_name like ?
            
            LIMIT 100
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Output the results as before
        if (count($contacts) > 0) {
            foreach ($contacts as $contact) {
                echo '<div class="contact-row d-flex flex-wrap align-items-center p-2 border-bottom" 
                        data-id="' . $contact['contact_id'] . '" 
                        style="cursor: pointer;">
                        <div class="col-12 col-md-3">' .
                    htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) .
                    '</div>
                        <div class="col-12 col-md-4">' .
                    htmlspecialchars($contact['email']) .
                    '</div>
                        <div class="col-12 col-md-3">' .
                    htmlspecialchars($contact['phone']) .
                    '</div>
                        <div class="col-12 col-md-2">' .
                    htmlspecialchars($contact['state']) .
                    '</div>
                    </div>';
            }
        } else {
            echo '<div class="p-3">No contacts found matching your search.</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="p-3 text-danger">Error performing search.</div>';
    }
}
