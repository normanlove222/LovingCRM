<?php
require_once 'init.php'; 
// Removed duplicate include of functions.php since init.php already loads it
// $config = include 'config.php';
// $db = $config['development']; // Change to 'production' if needed

// $pdo = new PDO(
//     $db['dsn'],
//     $db['username'],
//     $db['password'],
//     [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $db['charset']
//     ]
// );

//this file exports the contcts and tags only, to a .csv file. it wont backup the other tabes and files.

try {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create CSV file
    $output = fopen('php://output', 'w');

    // Output column headings
    fputcsv($output, [        
        'user_id',
        'first_name',
        'last_name',
        'email',       
        'phone',
        'mobile_phone',
        'address',
        'address2',
        'city',
        'state',
        'zip_code',
        'country',
        'company',
        'job_title',
        'date_of_birth',
        'website',
        'linkedin_profile',
        'twitter_handle',
        'lead_source',
        'lead_status',
        'notes',        
        'tags'
    ]);

    $sql = "SELECT 
                c.user_id,
                c.first_name,
                c.last_name,
                c.email,     
                c.phone,
                c.mobile_phone,
                c.address,
                c.address2,
                c.city,
                c.state,
                c.zip_code,
                c.country,
                c.company,
                c.job_title,
                c.date_of_birth,
                c.website,
                c.linkedin_profile,
                c.twitter_handle,
                c.lead_source,
                c.lead_status,
                c.notes,      
                GROUP_CONCAT(t.name SEPARATOR '|') AS tags
            FROM 
                contacts c
            LEFT JOIN 
                contact_tags ct ON c.contact_id = ct.contact_id
            LEFT JOIN 
                tags t ON ct.tag_id = t.tag_id
            GROUP BY 
                c.contact_id
    ";

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch and output data
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    // Close the output stream
    fclose($output);

    // Exit to prevent any further output
    exit();
} catch (Exception $e) {
    error_log("CSV Export Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error exporting contacts: " . $e->getMessage();
    exit();
}
