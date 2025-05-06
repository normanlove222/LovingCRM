<?php
require_once 'init.php';
require 'vendor/autoload.php';
$sendgrid = new \SendGrid(SG_API_KEY);

if (isset($_GET) && !empty($_GET)) {
    $tagArray = explode(',', $_GET['tags']);
    $email_id = $_GET['email_id'];
    // error_log("Email_id: " . $email_id);

    // Create placeholders for IN clause
    if (count($tagArray) === 1) {
        $placeholders = '?';
    } else {
        $placeholders = str_repeat('?,', count($tagArray) - 1) . '?';
    }

    // Function to check if a contact has the opt-out tag
    function contactHasOptOutTag($pdo, $contactId, $optOutTagId) {
        $stmt = $pdo->prepare("SELECT 1 FROM contact_tags WHERE contact_id = ? AND tag_id = ? LIMIT 1");
        $stmt->execute([$contactId, $optOutTagId]);
        return $stmt->fetchColumn() !== false;
    }

    // Fetch contacts by tags
    $sql = "SELECT DISTINCT c.contact_id, c.email FROM contacts c
            INNER JOIN contact_tags ct ON c.contact_id = ct.contact_id
            WHERE ct.tag_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tagArray);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // error_log("contacts: " . print_r($contacts, true));

    // Fetch the tag_id for the 'opt-out' tag
    $optOutTagStmt = $pdo->prepare("SELECT tag_id FROM tags WHERE name = 'opt-out' LIMIT 1");
    $optOutTagStmt->execute();
    $optOutTagId = $optOutTagStmt->fetchColumn();

    // Fetch email details
    $emailStmt = $pdo->prepare("SELECT * FROM emails WHERE email_id = ?");
    $emailStmt->execute([$email_id]);
    $email = $emailStmt->fetch(PDO::FETCH_ASSOC);
    // error_log("EMAIL before image tag added: " . print_r($email, true));

    // Start transaction
    $pdo->beginTransaction();

    try {
        $tos = [];
        foreach ($contacts as $contact) {
            // Skip contacts who have opted out
            if ($optOutTagId && contactHasOptOutTag($pdo, $contact['contact_id'], $optOutTagId)) {
                // error_log("Contact ID " . $contact['contact_id'] . " has opted out. Skipping email.");
                continue; // Skip to the next contact
            }

            // Insert into email_sendings
            $sendingStmt = $pdo->prepare("INSERT INTO email_sendings (email_id, contact_id, sent_at) VALUES (?, ?, NOW())");
            $sendingStmt->execute([$email_id, $contact['contact_id']]);
           
            $email_sending_id = $pdo->lastInsertId();
            // error_log("email_sending_id: " . $email_sending_id);
            
            // Add tracking pixel
            $trackingPixel = "<img src='https://" . SITE_DOMAIN_NAME . "/track/open?email_sending_id=$email_sending_id' alt='' width='1' height='1' style='display:none;' />";
            // error_log("trackingPixel: " . $trackingPixel);

            // Add tracking pixel before the closing body tag in the email's HTML
            $emailContent = preg_replace(
                '/<\/body>/i',
                $trackingPixel . '</body>',
                $email['html_content'],
                1
            );
            // error_log("Html after image added: " . $emailContent);
            // Rewrite links for click tracking, so we can tell who clicked the email
            $emailContent = preg_replace_callback('/<a href="(.*?)"/', function ($matches) use ($pdo, $email_id, $email_sending_id) {
                $originalUrl = $matches[1];
                $linkStmt = $pdo->prepare("INSERT INTO links (email_id, original_url, token) VALUES (?, ?, ?)");
                $token = bin2hex(random_bytes(16));
                $linkStmt->execute([$email_id, $originalUrl, $token]);
                $link_id = $pdo->lastInsertId();
                return "<a href='https://" . SITE_DOMAIN_NAME . "/track/click?email_sending_id=$email_sending_id&link_id=$link_id'";
            }, $emailContent);

            // Collect email addresses for bulk sending
            $tos[] = new \SendGrid\Mail\To($contact['email'], null, null, null, $emailContent);
        }

        // Prepare email
        $sg_email = new \SendGrid\Mail\Mail();
        $sg_email->setFrom(SEND_EMAIL, EMAIL_NAME);
        $sg_email->setSubject($email['subject']);
        $sg_email->addTos($tos);
        // Add HTML content to the email
        $sg_email->addContent("text/html", $emailContent);

        // Send email
        $response = $sendgrid->send($sg_email);
        // error_log("SendGrid Response contains: " . print_r($response, true));

        if ($response->statusCode() === 202) {
            // error_log("Emails sent successfully");
            // Commit transaction, if success statusCode
            $pdo->commit();
            // Update send info to table
            updateEmailsTable($email_id, $response, count($contacts));
            header('Location: emails.php?email=true');
            exit;
        } else {
            error_log("Email send failed with status code: " . $response->statusCode());
            echo json_encode(['success' => false, 'message' => $response->statusCode()]);
            throw new Exception("Failed to send emails");
        }
    } catch (Exception $e) {
        // Rollback transaction because we got an error code 300 and higher
        $pdo->rollBack();
        error_log("Exception caught: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}