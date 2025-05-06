<?php
require_once 'init.php'; // Ensures connection, functions, etc. are loaded
header('Content-Type: application/json'); // Crucial for AJAX response

$response = ['success' => false, 'message' => 'Invalid request.'];

// Basic validation: Check if POST request and action is set
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // You could check for specific actions here if needed, but POST is primary
    $response['message'] = 'Invalid request method.';
    http_response_code(405); // Method Not Allowed
    echo json_encode($response);
    exit;
}

if (!isset($_POST['action'])) {
    $response['message'] = 'Required action parameter missing.';
    http_response_code(400); // Bad Request
    echo json_encode($response);
    exit;
}


$action = $_POST['action'];
// Validate contact_id presence early for most actions
$contactId = isset($_POST['contact_id']) ? filter_var($_POST['contact_id'], FILTER_VALIDATE_INT) : null;

if (!$contactId && in_array($action, ['update_notes', 'update_contact'])) { // Check if action requires contact_id
    $response['message'] = 'Missing or invalid Contact ID.';
    http_response_code(400); // Bad Request
    echo json_encode($response);
    exit;
}

// --- Security Check (Optional but Recommended) ---
// Example: Check if user is logged in and has permission
/*
session_start(); // If using sessions
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required.';
    http_response_code(401); // Unauthorized
    echo json_encode($response);
    exit;
}
// You might also check if $_SESSION['user_id'] has rights to edit $contactId
*/

try {
    // Use a switch for better action handling if more actions are added later
    switch ($action) {
        case 'update_notes':
            if (!isset($_POST['notes'])) { // Check if notes data is present
                $response['message'] = 'Missing notes data.';
                http_response_code(400);
            } else {
                // PDO automatically handles escaping with prepared statements
                $notes = $_POST['notes'];
                $stmt = $pdo->prepare("UPDATE contacts SET notes = :notes, updated_at = NOW() WHERE contact_id = :id");
                $stmt->bindParam(':notes', $notes, PDO::PARAM_STR); // Explicitly set type if needed
                $stmt->bindParam(':id', $contactId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Notes updated successfully.';
                } else {
                    $response['message'] = 'Failed to update notes in database.';
                    error_log("Failed to update notes for contact ID: $contactId. PDO Error: " . implode(", ", $stmt->errorInfo()));
                    http_response_code(500); // Internal Server Error
                }
            }
            break; // End case 'update_notes'

        case 'update_contact':
            // --- Update Full Contact ---
            // Validate required fields server-side as well
            $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
            $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';

            if (empty($firstName) || empty($lastName)) {
                $response['message'] = 'First Name and Last Name are required.';
                http_response_code(400);
                break; // Exit case
            }

            // Collect all potential fields from the form
            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => isset($_POST['email']) ? trim($_POST['email']) : null,
                'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : null,
                'address' => isset($_POST['address']) ? trim($_POST['address']) : null,
                'city' => isset($_POST['city']) ? trim($_POST['city']) : null,
                'state' => isset($_POST['state']) ? trim($_POST['state']) : null,
                'zip_code' => isset($_POST['zip_code']) ? trim($_POST['zip_code']) : null,
                'company' => isset($_POST['company']) ? trim($_POST['company']) : null,
                // Add other fields from your edit form here if they exist
                // 'address2' => isset($_POST['address2']) ? trim($_POST['address2']) : null,
                // 'country' => isset($_POST['country']) ? trim($_POST['country']) : null,
                // etc...
                'contact_id' => $contactId // Needed for the WHERE clause param binding
            ];

            // Validate email format if provided and not empty
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'Invalid email format provided.';
                http_response_code(400);
                break; // Exit case
            }

            // Build SET part of SQL query dynamically based on received data
            $setParts = [];
            $params = []; // Parameters for execute()

            foreach ($data as $key => $value) {
                if ($key !== 'contact_id') { // Don't include contact_id in the SET clause
                    $setParts[] = "`" . $key . "` = :" . $key; // Use backticks for field names, placeholders for values
                    // Handle empty strings - store as NULL or empty string based on DB schema needs
                    // Example: Store empty string as NULL
                    $params[":$key"] = ($value === '' || $value === null) ? null : $value;
                    // Example: Store empty string as empty string
                    // $params[":$key"] = $value;
                }
            }
            $setParts[] = "`updated_at` = NOW()"; // Always update timestamp

            if (empty($setParts)) {
                $response['message'] = "No data provided for update.";
                http_response_code(400);
                break;
            }

            $sql = "UPDATE `contacts` SET " . implode(', ', $setParts) . " WHERE `contact_id` = :contact_id";
            $params[':contact_id'] = $contactId; // Add contact_id for the WHERE clause

            $stmt = $pdo->prepare($sql);

            if ($stmt->execute($params)) {
                $response['success'] = true;
                $response['message'] = 'Contact updated successfully.';
            } else {
                $response['message'] = 'Failed to update contact in database.';
                error_log("Failed to update contact ID: $contactId. SQL: $sql Params: " . print_r($params, true) . " PDO Error: " . implode(", ", $stmt->errorInfo()));
                http_response_code(500);
            }
            break; // End case 'update_contact'

        default:
            $response['message'] = 'Unknown or unsupported action requested.';
            http_response_code(400); // Bad Request for unknown action
            break;
    } // End switch ($action)

} catch (PDOException $e) {
    error_log("Database Error in ajax_update_contact.php: Action($action), ContactID($contactId) - " . $e->getMessage());
    $response['message'] = 'A database error occurred during the update.'; // User-friendly message
    http_response_code(500); // Internal Server Error
} catch (Exception $e) {
    // Catch other potential errors during processing
    error_log("General Error in ajax_update_contact.php: Action($action), ContactID($contactId) - " . $e->getMessage());
    $response['message'] = 'An unexpected server error occurred.';
    http_response_code(500);
}

// --- Output the JSON Response ---
// This will run regardless of success/failure inside the try block, sending the $response array
echo json_encode($response);
exit;
