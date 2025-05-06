<?php
//WE DONT NEED THIS AS SAVE_DESIGN.PHP IS DOING EVERYTHING. FILE CAN BE REMOVED!!

header('Content-Type: application/json');
require_once 'init.php';
// Retrieve JSON input from the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if both JSON design data and HTML content are provided
if (!isset($data['design']) || !isset($data['html'])) {
    echo json_encode(['success' => false, 'message' => 'Design JSON or HTML content is missing.']);
    exit;
}

// Assign the JSON and HTML data to variables for saving
$jsonContent = json_encode($data['design']); // JSON structure of the design
$htmlContent = $data['html'];                // HTML content
$title = $data['title'] ?? 'Untitled Email';
$title = $data['subject'] ?? 'No Subject yet';

try {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        // Update existing email
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE emails SET title = :title, json_content = :json_content, html_content = :html_content, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':json_content' => $jsonContent,
            ':html_content' => $htmlContent,
            ':id' => $id
        ]);
    } else {
        // Insert new email
        $stmt = $pdo->prepare("INSERT INTO emails (title, json_content, html_content, created_at, updated_at) VALUES (:title, :json_content, :html_content, NOW(), NOW())");
        $stmt->execute([
            ':title' => $title,
            ':json_content' => $jsonContent,
            ':html_content' => $htmlContent
        ]);
        $id = $pdo->lastInsertId();
    }

    // Send success response with redirect URL
    echo json_encode(['success' => true, 'redirect' => 'emails.php?success=success']);
    exit;
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
