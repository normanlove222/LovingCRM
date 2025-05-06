<?php
require_once('init.php');
include 'header.php';   

$max_columns = 22;
if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');

    // Start a transaction
    $pdo->beginTransaction();
    // console_log("Import Transaction started... files contain");

    try {
        // Read the header row
        $headers = fgetcsv($handle);

        // Check if the number of columns in the header matches the expected number
        if (count($headers) > $max_columns) {
            fclose($handle);
            echo "<h2 style='text-align: center;'>The CSV file has more columns than the expected " . $max_columns . '</h2>';
            echo "<h2 style='text-align: center;'>Go back and edit your .csv file to have less than the above mentioned number of columns.</h2>";
            exit;
        }

        // Prepare SQL statements
        $insertContactStmt = $pdo->prepare(
            "INSERT INTO contacts (user_id,
                                    first_name,
                                    last_name,
                                    email,    
                                    phone,
                                    mobile_phone,
                                    `address`,
                                    address2,
                                    city,
                                    `state`,
                                    zip_code,
                                    country,
                                    company,
                                    job_title,
                                    date_of_birth,
                                    website,
                                    linkedin_profile,
                                    twitter_handle,
                                    lead_source,
                                    lead_status,
                                    notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );



        $selectContactStmt = $pdo->prepare("SELECT contact_id FROM contacts WHERE email = ?");
        $insertTagStmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
        $selectTagStmt = $pdo->prepare("SELECT tag_id FROM tags WHERE name = ?");
        $insertContactTagStmt = $pdo->prepare("INSERT INTO contact_tags (contact_id, tag_id) VALUES (?, ?)");
        $selectContactTagStmt = $pdo->prepare("SELECT * FROM contact_tags WHERE contact_id = ? AND tag_id = ?");

        $insertedCount = 0; // Initialize a counter
        $skippedContacts = []; // Array to track skipped contacts
        while (($data = fgetcsv($handle)) !== FALSE
        ) {

            // Check if the number of columns matches the expected number
            // if (count($data) < 22) { // Adjust the number 28 to match the number of columns you expect
            //     console_log('$data count greater than 22, exiting loop ');
            //     continue;
            // }

            list(
                $user_id,
                $first_name,
                $last_name,
                $email,
                $phone,
                $mobile_phone,
                $address,
                $address2,
                $city,
                $state,
                $zip_code,
                $country,
                $company,
                $job_title,
                $date_of_birth,
                $website,
                $linkedin_profile,
                $twitter_handle,
                $lead_source,
                $lead_status,
                $notes,
                $tags
            ) = array_pad($data, 22, ""); // Ensure we have 22 elements, padding with empty strings if needed

            // Trim and validate data   
            
            $user_id = trim($user_id);
            $first_name = trim($first_name);
            $last_name = trim($last_name);
            $email = trim($email);
            $phone = trim($phone);
            $mobile_phone = trim($mobile_phone);
            $address = trim($address);
            $address2 = trim($address2);
            $city = trim($city);
            $state = trim($state);
            $zip_code = trim($zip_code);
            $country = trim($country);
            $company = trim($company);
            $job_title = trim($job_title);
            $date_of_birth = trim($date_of_birth);
            $website = trim($website);
            $linkedin_profile = trim($linkedin_profile);
            $twitter_handle = trim($twitter_handle);
            $lead_source = trim($lead_source);
            $lead_status = trim($lead_status);
            $notes = trim($notes);
            $tags = trim($tags);            

            // Check if contact exists
            $selectContactStmt->execute([$email]);
            $existing_contact = $selectContactStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_contact) {
                $contact_id = $existing_contact['contact_id'];
                // Track skipped contact
                $skippedContacts[] = [
                    'email' => $email,
                    'name' => $first_name . ' ' . $last_name
                ];
                // Optionally update contact information here
            } else {
                // console_log("were inside insert contacts " . $first_name);
                // Insert new contact
                $insertContactStmt->execute([
                    $user_id,
                    $first_name,
                    $last_name,
                    $email,
                    $phone,
                    $mobile_phone,
                    $address,
                    $address2,
                    $city,
                    $state,
                    $zip_code,
                    $country,
                    $company,
                    $job_title,
                    $date_of_birth,
                    $website,
                    $linkedin_profile,
                    $twitter_handle,
                    $lead_source,
                    $lead_status,
                    $notes
                ]);
                $contact_id = $pdo->lastInsertId();
                $insertedCount++; // Increment the counter
            }

            // Output the count of inserted contacts
            // echo "Number of contacts inserted: " . $insertedCount . "<br/>";
            // Process tags
            $tags = explode('|', $tags);
            // console_log("exploed tags : " . $tags);
            // echo "exploded tag: " . $tags . "<br/>";
            foreach ($tags as $tag_name) {
                $tag_name = trim($tag_name);
                if (empty($tag_name)) continue;

                // Debugging: Output the tag being processed
                // echo "Processing tag: " . $tag_name . "<br/>";

                // Check if tag exists
                $selectTagStmt->execute([$tag_name]);
                $tag = $selectTagStmt->fetch(PDO::FETCH_ASSOC);

                if (!$tag) {
                    // Insert new tag
                    $insertTagStmt->execute([$tag_name]);
                    $tag_id = $pdo->lastInsertId();
                } else {
                    $tag_id = $tag['tag_id'];
                }

                // Associate contact with tag
                $selectContactTagStmt->execute([$contact_id, $tag_id]);
                if (!$selectContactTagStmt->fetch()) {
                    $insertContactTagStmt->execute([$contact_id, $tag_id]);
                }
            } //end foreach loop
        }  //end while loop

        // Commit the transaction
        $pdo->commit();
        
        // Prepare redirect URL
        $redirectUrl = 'import.php?import=success';
        
        // Add skipped contacts info if any
        if (!empty($skippedContacts)) {
            // Store skipped contacts in session to avoid URL length limitations
            $_SESSION['skipped_contacts'] = $skippedContacts;
            $redirectUrl .= '&skipped=' . count($skippedContacts);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    } catch (Exception $e) {
        // Roll back the transaction on error
        $pdo->rollBack();
        // Redirect back to import page with error message
        $errorMessage = urlencode("Import failed: " . $e->getMessage());
        header('Location: import.php?import=error&message=' . $errorMessage);
        exit;
    }

    fclose($handle);
} //end if isset