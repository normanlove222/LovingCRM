<?php
// ajax_update_contact_list.php
require_once 'init.php'; // Include DB connection ($pdo)

header('Content-Type: application/json');

// --- Input Validation & Routing ---
$action = $_POST['action'] ?? null;
$list_id = filter_input(INPUT_POST, 'list_id', FILTER_VALIDATE_INT);

if (!$list_id || $list_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing List ID.']);
    exit;
}

// --- Handle 'remove_contacts' action ---
if ($action === 'remove_contacts') {
    if (empty($_POST['contact_ids_to_remove']) || !is_array($_POST['contact_ids_to_remove'])) {
        echo json_encode(['success' => false, 'message' => 'No Contact IDs provided for removal or invalid format.']);
        exit;
    }

    $contact_ids_raw_remove = $_POST['contact_ids_to_remove'];
    $validated_contact_ids_remove = [];

    // Validate IDs to remove are positive integers
    foreach ($contact_ids_raw_remove as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT) && (int)$id > 0) {
            $validated_contact_ids_remove[] = (int)$id;
        }
    }

    if (empty($validated_contact_ids_remove)) {
        echo json_encode(['success' => false, 'message' => 'No valid Contact IDs found for removal.']);
        exit;
    }

    // --- Database Operation ---
    try {
        $pdo->beginTransaction();

        // 1. Fetch the current contact_ids JSON, locking the row
        $stmt_select = $pdo->prepare("SELECT contact_ids FROM lists_of_contacts WHERE list_id = :list_id FOR UPDATE");
        $stmt_select->bindParam(':list_id', $list_id, PDO::PARAM_INT);
        $stmt_select->execute();
        $current_json = $stmt_select->fetchColumn();

        if ($current_json === false) { // Row not found
            throw new Exception("List with ID {$list_id} not found.");
        }

        // 2. Decode current IDs
        $current_ids = [];
        if (!empty($current_json) && $current_json !== 'null') {
            $decoded = json_decode($current_json, true);
            if (is_array($decoded)) {
                $current_ids = $decoded;
            } else {
                // Handle potentially invalid JSON already in the DB
                error_log("Invalid JSON encountered for list_id {$list_id} during update: " . $current_json);
                // Decide recovery strategy: overwrite with empty or fail? Failing is safer.
                throw new Exception("Invalid data format found for the list's contacts.");
            }
        }

        // 3. Remove the specified IDs
        // array_diff returns values from $current_ids that are NOT present in $validated_contact_ids_remove
        $updated_ids = array_diff($current_ids, $validated_contact_ids_remove);
        // Re-index array if needed, although JSON encode handles non-sequential keys
        $updated_ids = array_values($updated_ids);

        // 4. Encode the updated array back to JSON
        $updated_json = json_encode($updated_ids);
        if ($updated_json === false) {
            throw new Exception("Failed to re-encode contact IDs.");
        }

        // 5. Update the list in the database
        $stmt_update = $pdo->prepare("UPDATE lists_of_contacts SET contact_ids = :new_json WHERE list_id = :list_id");
        $stmt_update->bindParam(':new_json', $updated_json, PDO::PARAM_STR);
        $stmt_update->bindParam(':list_id', $list_id, PDO::PARAM_INT);

        if (!$stmt_update->execute()) {
            throw new PDOException("Failed to execute update statement.");
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'List updated successfully.']);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("DB Error in ajax_update_contact_list.php (Action: {$action}): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error during update.']);
    } catch (JsonException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("JSON Error in ajax_update_contact_list.php (Action: {$action}): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Data processing error during update.']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("General Error in ajax_update_contact_list.php (Action: {$action}): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // Handle other actions if you add them later (e.g., 'rename_list')
    echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
}