<?php
require_once 'functions.php'; // Ensure you have database connection and necessary functions

// Parse the request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route the request based on the path. below path is used for eimg tags src to serve as a transparant image to track when  email is opened. as opening will trigger the img to open or pin its path, which has these endpoints and will trigger this code. Clients like Gmail however, cache these images for security reasons and this falsly pings and is registered as an open. 
if ($path === '/track/open') {
    if (isset($_GET['email_sending_id'])) {
        $email_sending_id = $_GET['email_sending_id'];

        // Capture IP address and user agent
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        try {
            // Check if email_sending_id exists in email_sendings table
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM email_sendings WHERE id = ?");
            $checkStmt->execute([$email_sending_id]);
            $exists = $checkStmt->fetchColumn();

            if ($exists) {
                // Register the email opening in the database with IP address and user agent
                $stmt = $pdo->prepare("INSERT INTO email_openings (email_sending_id, opened_at, ip_address, user_agent) VALUES (?, NOW(), ?, ?)");
                $stmt->execute([$email_sending_id, $ip_address, $user_agent]);

                echo "Email opened for email_sending_id: " . htmlspecialchars($email_sending_id);
            } else {
                error_log("Invalid email_sending_id: " . $email_sending_id);
                http_response_code(400);
                echo "Invalid email_sending_id";
            }
        } catch (PDOException $e) {
            error_log("Error inserting email opening: " . $e->getMessage());
            http_response_code(500);
            echo "Error processing request";
        }
    } else {
        http_response_code(400);
        echo "Missing email_sending_id";
    }
} elseif ($path === '/track/click') {
    if (isset($_GET['email_sending_id']) && isset($_GET['link_id'])) {
        $email_sending_id = $_GET['email_sending_id'];
        $link_id = $_GET['link_id'];

        try {
            // Debugging: Log the values
            error_log("email_sending_id: $email_sending_id, link_id: $link_id");

            // Check if email_sending_id exists in email_sendings table
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM email_sendings WHERE id = ?");
            $checkStmt->execute([$email_sending_id]);
            $emailExists = $checkStmt->fetchColumn();

            // Check if link_id exists in links table
            $linkCheckStmt = $pdo->prepare("SELECT original_url FROM links WHERE links_id = ?");
            $linkCheckStmt->execute([$link_id]);
            $originalUrl = $linkCheckStmt->fetchColumn();

            if ($emailExists && $originalUrl) {
                // Register the link click in the database
                $stmt = $pdo->prepare("INSERT INTO link_clicks (email_sending_id, link_id, clicked_at) VALUES (?, ?, NOW())");
                $stmt->execute([$email_sending_id, $link_id]);

                // Redirect to the original URL
                header("Location: $originalUrl");
                exit;
            } else {
                error_log("Invalid email_sending_id or link_id: " . $email_sending_id . ", " . $link_id);
                http_response_code(400);
                echo "Invalid email_sending_id or link_id";
            }
        } catch (PDOException $e) {
            error_log("Error inserting link click: " . $e->getMessage());
            http_response_code(500);
            echo "Error processing request";
        }
    } else {
        http_response_code(400);
        echo "Missing parameters";
    }
} else {
    http_response_code(404);
    echo "Endpoint not found";
}