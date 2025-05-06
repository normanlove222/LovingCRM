<?php
require_once('init.php');


include 'header.php';
include 'menu.php';
?>
<div class="container mt-4">

    <h1>Import Contacts</h1>

    <?php
    // Display success or error messages based on URL parameters
    if (isset($_GET['import'])) {
        if ($_GET['import'] == 'success') {
            echo '<div class="alert alert-success" role="alert">Contacts imported successfully!';
            
            // Check if any contacts were skipped
            if (isset($_GET['skipped']) && $_GET['skipped'] > 0) {
                $skippedCount = (int)$_GET['skipped'];
                echo ' However, ' . $skippedCount . ' contact(s) were skipped because their emails already exist in the database.';
                
                // Display skipped contacts if available in session
                if (isset($_SESSION['skipped_contacts']) && !empty($_SESSION['skipped_contacts'])) {
                    echo '<div class="mt-2"><strong>Skipped contacts:</strong>';
                    echo '<ul class="mb-0">';
                    foreach ($_SESSION['skipped_contacts'] as $contact) {
                        echo '<li>' . htmlspecialchars($contact['name']) . ' (' . htmlspecialchars($contact['email']) . ')</li>';
                    }
                    echo '</ul></div>';
                    
                    // Clear the session data after displaying
                    unset($_SESSION['skipped_contacts']);
                }
            }
            
            echo '</div>';
        } elseif ($_GET['import'] == 'error' && isset($_GET['message'])) {
            $errorMessage = htmlspecialchars(urldecode($_GET['message'])); // Decode and sanitize
            echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>';
        }
    }
    ?>

    <p> In order to Import contacts, your .csv file MUST use the import_Sample.csv file format and headings. In order to be imported your, data must fit what this app expects. Any difference will result in the import failing. If you have extra columns, or use different column names, you must edit your data. You can download the sample by clicking <a href='import_sample.csv'>here</a> </p>
    <p> Take extra care to notice how the tags are expected to appear in the tag column for each contact, which is to seperate the tag names with the pipe character, i.e. vip|customer care |buyer|many buys|family|doctor. The import process will setup the tags for that contact.</p>


    <body>
        <!-- Main layout -->
        <div class="container-fluid">
            <form action="process_import.php" method="post" enctype="multipart/form-data" class="mb-3">
                <div class="mb-3">
                    <label for="csvFile" class="form-label">Select CSV File:</label>
                    <input type="file" name="csv_file" id="csvFile" class="form-control">
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Import</button>
            </form>



            <?php
            include 'footer.php';
            include 'scripts.php';
            ?>

            <script>

            </script>
    </body>

    </html>