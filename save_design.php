<?php
header('Content-Type: application/json');

try {
    require_once 'init.php';

    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check if both JSON design data and HTML content are provided
    if (!isset($data['design']) || !isset($data['html'])) {
        echo json_encode(['success' => false, 'message' => 'Design JSON or HTML content is missing.']);
        exit;
    }

    // Assign the JSON and HTML data to variables for saving
    $jsonContent = json_encode($data['design']);
    $htmlContent = $data['html'];
    $title = $data['title'] ?? 'Untitled Email';
    $subject = $data['subject'] ?? 'No Subject yet';

    if (isset($data['email_id']) && !empty($data['email_id'])) {
        // Update existing email
        $id = (int)$data['email_id'];
        $stmt = $pdo->prepare("UPDATE emails SET title = :title, subject = :subject, json_content = :json_content, html_content = :html_content, updated_at = NOW() WHERE email_id = :id");
        $stmt->execute([
            ':title' => $title,
            ':subject' => $subject,
            ':json_content' => $jsonContent,
            ':html_content' => $htmlContent,
            ':id' => $id
        ]);
    } else {
        // Insert new email
        $stmt = $pdo->prepare("INSERT INTO emails (title, subject, json_content, html_content, created_at, updated_at) VALUES (:title, :subject, :json_content, :html_content, NOW(), NOW())");
        $stmt->execute([
            ':title' => $title,
            ':subject' => $subject,
            ':json_content' => $jsonContent,
            ':html_content' => $htmlContent
        ]);
    }

    echo json_encode(['success' => true, 'redirect' => 'emails.php']);
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error server-side
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}