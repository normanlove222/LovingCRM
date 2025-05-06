<?php
// ajax_delete_contact_lists.php
require_once 'init.php'; // Include DB connection ($pdo)

header('Content-Type: application/json');

// Input Validation
if (empty($_POST['list_ids']) || !is_array($_POST['list_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No list IDs provided or invalid format.']);
    exit;
}

$list_ids_raw = $_POST['list_ids'];
$validated_list_ids = [];

// Validate IDs are positive integers
foreach ($list_ids_raw as $id) {
    if (filter_var($id, FILTER_VALIDATE_INT) && (int)$id > 0) {
        $validated_list_ids[] = (int)$id;
    }
}

if (empty($validated_list_ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid list IDs found for deletion.']);
    exit;
}

// Database Operation
try {
    $pdo->beginTransaction();

    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($validated_list_ids), '?'));

    // Prepare DELETE statement targeting the correct table
    $sql = "DELETE FROM lists_of_contacts WHERE list_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // Execute with the validated IDs
    if (!$stmt->execute($validated_list_ids)) {
        throw new PDOException("Failed to execute delete statement.");
    }

    // Check how many rows were affected (optional but good)
    $affected_rows = $stmt->rowCount();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully deleted {$affected_rows} list(s)."
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database Error in ajax_delete_contact_lists.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error during deletion.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General Error in ajax_delete_contact_lists.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
