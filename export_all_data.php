<?php
require_once('init.php');

try {
    // Set headers for SQL download
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup.sql"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Get all tables in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Get CREATE TABLE statement
        $createTableStmt = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
        echo $createTableStmt['Create Table'] . ";\n\n";

        // Get table data
        $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $columns = array_keys($row);
            $values = array_map(function($value) use ($pdo) {
                return is_null($value) ? 'NULL' : $pdo->quote($value);
            }, array_values($row));
            echo "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
        }

        echo "\n\n";
    }

    // Exit to prevent any further output
    exit();
} catch (Exception $e) {
    error_log("Export Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error exporting data: " . $e->getMessage();
    exit();
}
