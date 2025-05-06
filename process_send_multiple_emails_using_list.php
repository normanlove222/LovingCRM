<?php
require_once 'init.php';
require 'vendor/autoload.php';
$sendgrid = new \SendGrid(SG_API_KEY);

ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error.log');

if (isset($_GET) && !empty($_GET)) {
    $listId = $_GET['listId'];
    $email_id = $_GET['email_id'];

    
}

// Get email content
$ContentQuery = "SELECT subject, html_content FROM emails WHERE email_id = ?";
$stmt = $pdo->prepare($ContentQuery);
$stmt->execute([$email_id]);
$email = $stmt->fetch(PDO::FETCH_ASSOC);

// Get contacts from just the list_id. Used Minstral to get this complex query because tags in lists table are a json string and have to match agains integer tag_ids from other 2 tables. not easy to do . 
//Claude and ChatGPT couldnt do it. 
$users = "
            SELECT c.first_name, c.email
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
            ) AS distinct_contacts ON c.contact_id = distinct_contacts.contact_id;
             ";

$stmt = $pdo->prepare($users);
$stmt->execute([$listId]);
$emailData = $stmt->fetchAll(PDO::FETCH_ASSOC);
// error_log('EmailData is: ' . print_r($emailData, true));
$count = count($emailData); // Add this line to get count of returned rows
// error_log('count of emails: ' . $count);
$tos = mapForSendGrid($emailData);
// error_log('tos are: ' . print_r($tos, true));

// error_log('we just converted to tos: ' . print_r($tos, true));
//Step 3: setup email with SendGrid

try {
    error_log("SendGrid response: " . print_r($response, true));

    // In process_send_multiple_emails.php
    if ($response->statusCode() === 202) {
        error_log("Emails sent successfully");
        //update send info to table
        updateEmailsTable($email_id, $response, $count);
        header('Location: emails.php?email=true');
        exit;
    } else {
        error_log("Email send failed with status code: " . $response->statusCode());
        echo json_encode(['success' => false, 'message' => $response->statusCode()]);
        throw new Exception("Failed to send emails");
    }
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
