<?php
require_once 'init.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

// Determine if data is sent as JSON or form-urlencoded
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}


$action = isset($input['action']) ? $input['action'] : null;
$contactId = isset($input['contact_id']) ? filter_var($input['contact_id'], FILTER_VALIDATE_INT) : null;

if (!$action || !$contactId) {
    $response['message'] = 'Missing required parameters (action or contact_id).';
    echo json_encode($response);
    exit;
}

// --- Security Check (Optional) ---
// Verify user permission if needed

try {
    $pdo->beginTransaction(); // Use transactions for multi-step operations

    if ($action === 'add_tag') {
        $tagName = isset($input['tag_name']) ? trim($input['tag_name']) : null;
        if (empty($tagName)) {
            $response['message'] = 'Tag name cannot be empty.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }

        // 1. Find or Create the Tag
        $stmt_find = $pdo->prepare("SELECT tag_id FROM tags WHERE name = :name");
        $stmt_find->bindParam(':name', $tagName);
        $stmt_find->execute();
        $tag = $stmt_find->fetch(PDO::FETCH_ASSOC);

        $tagId = null;
        if ($tag) {
            $tagId = $tag['tag_id'];
        } else {
            // Tag doesn't exist, create it
            $stmt_create = $pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
            $stmt_create->bindParam(':name', $tagName);
            if ($stmt_create->execute()) {
                $tagId = $pdo->lastInsertId();
            } else {
                $response['message'] = 'Failed to create new tag.';
                error_log("Failed to create tag '$tagName'. PDO Error: " . implode(", ", $stmt_create->errorInfo()));
                $pdo->rollBack();
                echo json_encode($response);
                exit;
            }
        }

        // 2. Check if the association already exists
        $stmt_check_assoc = $pdo->prepare("SELECT 1 FROM contact_tags WHERE contact_id = :contact_id AND tag_id = :tag_id");
        $stmt_check_assoc->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
        $stmt_check_assoc->bindParam(':tag_id', $tagId, PDO::PARAM_INT);
        $stmt_check_assoc->execute();

        if ($stmt_check_assoc->fetchColumn()) {
            // Association already exists
            $response['success'] = true; // Still consider it a success from user perspective
            $response['message'] = 'Contact already has this tag.';
            $pdo->commit(); // Commit transaction even if no change
            echo json_encode($response);
            exit;
        }


        // 3. Associate Tag with Contact
        $stmt_assoc = $pdo->prepare("INSERT INTO contact_tags (contact_id, tag_id) VALUES (:contact_id, :tag_id)");
        $stmt_assoc->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
        $stmt_assoc->bindParam(':tag_id', $tagId, PDO::PARAM_INT);

        if ($stmt_assoc->execute()) {
            $response['success'] = true;
            $response['message'] = 'Tag added successfully.';
            $pdo->commit();
        } else {
            $response['message'] = 'Failed to associate tag with contact.';
            error_log("Failed to associate tag ID $tagId with contact ID $contactId. PDO Error: " . implode(", ", $stmt_assoc->errorInfo()));
            $pdo->rollBack();
        }
    } elseif ($action === 'remove_tags') {
        $tagIds = isset($input['tag_ids']) && is_array($input['tag_ids']) ? $input['tag_ids'] : null;

        if (empty($tagIds)) {
            $response['message'] = 'No tag IDs provided for removal.';
            $pdo->rollBack(); // No changes needed, but good practice
            echo json_encode($response);
            exit;
        }

        // Sanitize tag IDs
        $sanitizedTagIds = array_map('intval', $tagIds);
        $sanitizedTagIds = array_filter($sanitizedTagIds, fn($id) => $id > 0);

        if (empty($sanitizedTagIds)) {
            $response['message'] = 'No valid tag IDs provided.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }

        // Prepare placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($sanitizedTagIds), '?'));
        $params = array_merge([$contactId], $sanitizedTagIds); // Combine contact ID and tag IDs for execute

        $sql = "DELETE FROM contact_tags WHERE contact_id = ? AND tag_id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute($params)) {
            $response['success'] = true;
            $response['message'] = 'Selected tags removed successfully.';
            $pdo->commit();
        } else {
            $response['message'] = 'Failed to remove tags.';
            error_log("Failed to remove tags for contact ID $contactId. Tag IDs: " . implode(',', $sanitizedTagIds) . ". PDO Error: " . implode(", ", $stmt->errorInfo()));
            $pdo->rollBack();
        }
    } else {
        $response['message'] = 'Unknown action specified.';
        $pdo->rollBack(); // Rollback if action is unknown
    }
} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback on any database error
    error_log("Error in ajax_manage_tags.php: Action($action), ContactID($contactId) - " . $e->getMessage());
    $response['message'] = 'A database error occurred.'; // User-friendly message
}

echo json_encode($response);
exit;
