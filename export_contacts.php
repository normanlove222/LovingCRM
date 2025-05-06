<?php
header('Content-Type: application/json'); // Default content type

try {
    require_once 'init.php';

    $tagName = $_POST['tagId']; // This is actually the tag name now
    $tagId = getTagIdByName($pdo, $tagName);

    if (!$tagId) {
        throw new Exception("Tag not found");
    }

    $sql = "SELECT c.* FROM contacts c 
            INNER JOIN contact_tags ct ON c.contact_id = ct.contact_id 
            WHERE ct.tag_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tagId]);

    ob_start(); // Start output buffering

    $output = fopen('php://output', 'w');
    fputcsv($output, ['First Name', 'Last Name', 'Email', 'Phone', 'Address', 'Address 2', 'City', 'State', 'Zip']);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['phone'],
            $row['address'],
            $row['address2'],
            $row['city'],
            $row['state'],
            $row['zip_code']
        ]);
    }

    fclose($output);

    $csvContent = ob_get_clean(); // Get the CSV content

    echo json_encode([
        'success' => true,
        'data' => $csvContent,
        'filename' => 'contacts.csv'
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
