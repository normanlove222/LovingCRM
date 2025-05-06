<?php
require_once('init.php');
// Optional: Ensure user is authenticated
// require_login();  // Uncomment if you have an authentication function
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: emails.php?error=invalid_request_method');
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: emails.php?error=invalid_csrf');
    exit;
}

// Simple rate limiting: allow one clone every 5 seconds per session
if (isset($_SESSION['last_clone_time']) && (time() - $_SESSION['last_clone_time'] < 5)) {
    header('Location: emails.php?error=rate_limit');
    exit;
}
$_SESSION['last_clone_time'] = time();

// Validate email_id
if (!isset($_POST['email_id']) || empty($_POST['email_id'])) {
    header('Location: emails.php?error=invalid_id');
    exit;
}

try {
    // Get original email data
    $stmt = $pdo->prepare("SELECT title, subject, json_content, html_content FROM emails WHERE email_id = ?");
    $stmt->execute([$_POST['email_id']]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$email) {
        header('Location: emails.php?error=email_not_found');
        exit;
    }

    // Insert cloned email with modified title
    $stmt = $pdo->prepare("INSERT INTO emails (title, subject, json_content, html_content) 
                          VALUES (:title, :subject, :json_content, :html_content)");

    $stmt->execute([
        ':title' => 'Copy of ' . $email['title'],
        ':subject' => $email['subject'],
        ':json_content' => $email['json_content'],
        ':html_content' => $email['html_content']
    ]);

    header('Location: emails.php?clone=success');
    exit;
} catch (PDOException $e) {
    header('Location: emails.php?error=clone_failed');
    exit;
}
?>
