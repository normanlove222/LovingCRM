<?php
// ajax_save_list_from_selection.php
require_once 'init.php'; // Ensure this includes your DB connection ($pdo) and functions

header('Content-Type: application/json');

// --- Input Validation ---
if (empty($_POST['list_name'])) {
    echo json_encode(['success' => false, 'message' => 'List name is required.']);
    exit;
}
// Ensure selected_contact_ids exists and is an array before proceeding
if (!isset($_POST['selected_contact_ids']) || !is_array($_POST['selected_contact_ids'])) {
    // Changed the check slightly to handle cases where it might be present but not an array
    echo json_encode(['success' => false, 'message' => 'Contact IDs are missing or invalid.']);
    exit;
}
// Only proceed if the array is not empty
if (empty($_POST['selected_contact_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No contact IDs were provided.']);
    exit;
}


$list_name = trim($_POST['list_name']);
$contact_ids_raw = $_POST['selected_contact_ids'];

// Further validation: ensure contact IDs are positive integers
$validated_contact_ids = [];
foreach ($contact_ids_raw as $id) {
    if (filter_var($id, FILTER_VALIDATE_INT) && (int)$id > 0) {
        $validated_contact_ids[] = (int)$id;
    } else {
        error_log("Invalid contact ID received in ajax_save_list_from_selection.php: " . print_r($id, true));
    }
}

// Ensure we still have valid IDs after filtering
if (empty($validated_contact_ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid contact IDs provided after validation.']);
    exit;
}

// Encode the validated IDs as a JSON string
$contact_ids_json = json_encode($validated_contact_ids);
if ($contact_ids_json === false) {
    error_log("JSON encoding failed in ajax_save_list_from_selection.php for IDs: " . print_r($validated_contact_ids, true));
    echo json_encode(['success' => false, 'message' => 'Could not process the contact IDs.']);
    exit;
}

// --- Database Interaction ---
try {
    // Use a transaction for atomicity (all or nothing)
    $pdo->beginTransaction();

    // *** MODIFIED: Target the new 'lists_of_contacts' table ***
    // *** MODIFIED: Insert into 'list_name' and 'contact_ids' columns ***
    // Assuming 'lists_of_contacts' has columns: list_id (auto), list_name, contact_ids (JSON), created_at, updated_at
    $sql_insert_list = "INSERT INTO lists_of_contacts (list_name, contact_ids, created_at)
                        VALUES (:list_name, :contact_ids, NOW())";

    $stmt_list = $pdo->prepare($sql_insert_list);

    // Bind parameters
    $stmt_list->bindParam(':list_name', $list_name, PDO::PARAM_STR);
    $stmt_list->bindParam(':contact_ids', $contact_ids_json, PDO::PARAM_STR); // Bind JSON as string

    // Execute the insertion
    if (!$stmt_list->execute()) {
        // Get error info for logging
        $errorInfo = $stmt_list->errorInfo();
        throw new PDOException("Failed to execute list insertion statement. Error: " . ($errorInfo[2] ?? 'Unknown error'));
    }

    // If execution was successful, commit the transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'List saved successfully!']);
} catch (PDOException $e) {
    // Rollback the transaction on any database error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the detailed error for debugging
    error_log("Database Error in ajax_save_list_from_selection.php: " . $e->getMessage());
    // Send a user-friendly error message back
    echo json_encode(['success' => false, 'message' => 'A database error occurred while saving the list. The issue has been logged.']);
} catch (Exception $e) {
    // Catch other potential errors
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General Error in ajax_save_list_from_selection.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
}