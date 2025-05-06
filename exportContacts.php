<?php
require_once 'init.php'; // Include your database connection and core functions

// --- Security Check (Optional but Recommended) ---
/*
if (!isUserLoggedIn()) { // Replace with your actual check
    header('HTTP/1.1 403 Forbidden');
    echo 'Access Denied';
    exit;
}
*/

// --- Process Input ---
// This block should be around line 7 - Note: NO 'tagId' logic here.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['selected_contact_ids']) || !is_array($_POST['selected_contact_ids'])) {
    // Invalid request or no data
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid export request. Please select contacts from the search results page.';
    exit;
}

$selected_ids = $_POST['selected_contact_ids'];

// Sanitize the IDs to ensure they are integers
$sanitized_ids = array_map('intval', $selected_ids);
$sanitized_ids = array_filter($sanitized_ids, function ($id) {
    return $id > 0;
}); // Remove zeros or invalid values

if (empty($sanitized_ids)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'No valid contact IDs provided for export.';
    exit;
}

// --- Fetch Contact Data ---
try {
    // Prepare the IN clause placeholders
    $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));

    // Query to fetch all relevant details for the selected contacts
    // ** IMPORTANT: Adjust columns below to match EXACTLY what you want in your CSV export **
    $sql = "SELECT
                contact_id, first_name, last_name, email, phone,
                address, address2, city, state, zip_code, country,
                company, job_title, website,
                lead_source, lead_status, email_status,
                notes, created_at, updated_at
                /* Add or remove columns as needed */
            FROM contacts
            WHERE contact_id IN ($placeholders)
            ORDER BY last_name, first_name"; // Optional: Order the export

    $stmt = $pdo->prepare($sql);
    // PDO correctly binds parameters even for IN clauses when executed this way
    $stmt->execute($sanitized_ids);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error in export_contacts.php: " . $e->getMessage()); // Log the detailed error
    header('HTTP/1.1 500 Internal Server Error');
    // Don't echo detailed errors to the user in production
    echo 'An error occurred while fetching contact data for export. Please check server logs.';
    exit;
}

// --- Generate CSV ---
if (empty($contacts)) {
    // This shouldn't happen if IDs were valid, but handle it
    // Send a plain text message instead of triggering a download
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No contact data found for the selected IDs.';
    exit;
}

// Define CSV filename
$filename = "exported_contacts_" . date('Y-m-d_His') . ".csv"; // Added timestamp for uniqueness

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
// Prevent caching
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream directly to the browser response
$output = fopen('php://output', 'w');
if ($output === false) {
    error_log("Failed to open php://output stream in export_contacts.php");
    header('HTTP/1.1 500 Internal Server Error');
    // Don't echo detailed errors
    echo 'Failed to initiate file download stream.';
    exit;
}

// --- Add CSV Header Row ---
// Use keys from the first fetched contact record as headers
// This ensures headers match the actual data columns selected in the SQL query
fputcsv($output, array_keys($contacts[0]));

// --- Add Data Rows ---
foreach ($contacts as $contact) {
    // $contact is already an associative array matching the headers
    fputcsv($output, $contact);
}

// --- Close the output stream (optional for php://output, but good practice) ---
// fclose($output); // Uncomment if needed, though usually not necessary here

exit; // Ensure no extra output (like HTML, warnings, or the stray JSON) interferes