<?php
// display_list_contacts.php
// Displays contacts belonging to a specific list stored in lists_of_contacts table

require_once 'init.php'; // Ensure $pdo is available

// --- Input Processing and Data Fetching ---
$list_id = filter_input(INPUT_GET, 'list_id', FILTER_VALIDATE_INT);
$list_info = null;
$contacts = [];
$error_message = null;
$contact_count = 0;
$list_name_display = 'Unknown List'; // Default

if (!$list_id || $list_id <= 0) {
    $error_message = "Invalid or missing list ID provided.";
} else {
    try {
        // 1. Fetch List Info (Name and Contact IDs JSON) from the correct table
        $stmt_list = $pdo->prepare("SELECT list_name, contact_ids FROM lists_of_contacts WHERE list_id = :list_id");
        $stmt_list->bindParam(':list_id', $list_id, PDO::PARAM_INT);
        $stmt_list->execute();
        $list_info = $stmt_list->fetch(PDO::FETCH_ASSOC);

        if (!$list_info) {
            $error_message = "The requested list (ID: {$list_id}) was not found.";
        } else {
            $list_name_display = htmlspecialchars($list_info['list_name']); // Set list name for display

            // 2. Decode Contact IDs
            $contact_ids = [];
            if (!empty($list_info['contact_ids']) && $list_info['contact_ids'] !== 'null') { // Check for non-empty, non-null JSON
                $decoded_ids = json_decode($list_info['contact_ids'], true); // Decode as associative array

                // Validate decoded IDs are integers > 0
                if (is_array($decoded_ids)) {
                    foreach ($decoded_ids as $id) {
                        if (is_numeric($id) && (int)$id > 0) {
                            $contact_ids[] = (int)$id; // Build array of valid integer IDs
                        }
                    }
                } else {
                    // Log if JSON is invalid but not empty/null
                    error_log("Invalid JSON structure in contact_ids for list_id {$list_id}: " . $list_info['contact_ids']);
                }
            }

            // 3. Fetch Contact Details if IDs exist
            if (!empty($contact_ids)) {
                $contact_count = count($contact_ids);
                // Create placeholders: ?, ?, ?
                $placeholders = implode(',', array_fill(0, $contact_count, '?'));

                // Prepare query to get contact details
                $sql_contacts = "SELECT contact_id, first_name, last_name, email, phone, state
                                 FROM contacts
                                 WHERE contact_id IN ($placeholders)
                                 ORDER BY first_name, last_name";

                $stmt_contacts = $pdo->prepare($sql_contacts);

                // Bind each contact ID individually (more secure than embedding in string)
                foreach ($contact_ids as $k => $id) {
                    $stmt_contacts->bindValue(($k + 1), $id, PDO::PARAM_INT);
                }

                $stmt_contacts->execute();
                $contacts = $stmt_contacts->fetchAll(PDO::FETCH_ASSOC);
                // Update count based on actual found contacts (might be less if contacts were deleted)
                $contact_count = count($contacts);
            } else {
                // List exists but has no valid contact IDs associated
                $contacts = []; // Ensure contacts is an empty array
                $contact_count = 0;
            }
        }
    } catch (PDOException $e) {
        error_log("Database Error in display_list_contacts.php for list_id {$list_id}: " . $e->getMessage());
        $error_message = "A database error occurred retrieving list details.";
    } catch (JsonException $e) { // Catch JSON decoding errors specifically
        error_log("JSON Decode Error in display_list_contacts.php for list_id {$list_id}: " . $e->getMessage());
        $error_message = "Error processing the list's contact data format.";
    } catch (Exception $e) { // Catch any other general errors
        error_log("General Error in display_list_contacts.php for list_id {$list_id}: " . $e->getMessage());
        $error_message = "An unexpected error occurred.";
    }
}

// --- HTML Display ---
include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error_message); ?>
            <br><a href="view_contact_lists.php" class="btn btn-secondary btn-sm mt-2">Back to Lists</a>
        </div>
    <?php else: // Only proceed if no critical error occurred 
    ?>
        <h1 class="text-center">Contacts in list:</h1>


        <h2 class="text-center"><?php echo $list_name_display; ?></h2>
        <p class="text-muted"><span id="contact-count-display"><?php echo $contact_count; ?></span> contact(s) in this list.</p>

        <!-- Actions Dropdown for Contacts in THIS list -->
        <div class="button-group mt-4 mb-3">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="contactActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" <?php echo empty($contacts) ? 'disabled' : ''; ?>>
                    Actions for Selected Contacts
                </button>
                <ul class="dropdown-menu" aria-labelledby="contactActionsDropdown">
                    <li><button class="dropdown-item" onclick="removeSelectedFromList(<?php echo $list_id; ?>)" type="button">Remove From This List</button></li>

                    <li><button class="dropdown-item" onclick="exportSelectedListContacts()" type="button">Export Selected</button></li>
                    <li><button class="dropdown-item" onclick="createCampaignFromSelected()" type="button">Create Campaign</button></li>
                </ul>
            </div>
            <a href="view_contacts_lists.php" class="btn btn-outline-secondary ms-2">Back to All Lists</a>
        </div>

        <!-- Contacts Table -->
        <div id="list-contacts-table" class="table-responsive">
            <?php if (!empty($contacts)): ?>
                <table class="table table-hover searchTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;"><input type="checkbox" class="form-check-input" id="select-all-contacts" title="Select/Deselect All"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th style="width: 10%;">State</th>
                        </tr>
                    </thead>
                    <tbody id="list-contacts-body">
                        <?php foreach ($contacts as $contact):
                            // Sanitize all output from the database
                            $contactId = htmlspecialchars($contact['contact_id'] ?? '');
                            $firstName = htmlspecialchars($contact['first_name'] ?? '');
                            $lastName = htmlspecialchars($contact['last_name'] ?? '');
                            $email = htmlspecialchars($contact['email'] ?? '');
                            $phone = htmlspecialchars($contact['phone'] ?? '');
                            $state = htmlspecialchars($contact['state'] ?? '');
                        ?>
                            <tr class="contact-row-item" data-contact-id="<?php echo $contactId; ?>">
                                <td><input type="checkbox" class="contact-list-checkbox form-check-input" value="<?php echo $contactId; ?>"></td>
                                <td><?php echo trim($firstName . ' ' . $lastName); ?></td>
                                <td><?php echo $email; ?></td>
                                <td><?php echo $phone; ?></td>
                                <td><?php echo $state; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($list_info && empty($contact_ids)): // List exists but JSON was empty/null 
            ?>
                <div class="alert alert-info mt-3">This list is currently empty.</div>
            <?php elseif ($list_info && !empty($contact_ids) && empty($contacts)): // JSON had IDs but no matching contacts found 
            ?>
                <div class="alert alert-warning mt-3">Could not find the contacts associated with this list (they may have been deleted).</div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div> <!-- /container -->

<?php
include 'footer.php';
include 'scripts.php'; // Ensure jQuery, Bootstrap JS, SweetAlert are included
?>

<script>
    $(document).ready(function() {
        // --- Event Listeners ---
        // Select All Checkbox for contacts
        $('#select-all-contacts').on('change', function() {
            $('.contact-list-checkbox').prop('checked', this.checked);
        });

        // Individual contact checkbox change
        $('#list-contacts-body').on('change', '.contact-list-checkbox', function() {
            if (!this.checked) {
                $('#select-all-contacts').prop('checked', false);
            } else {
                // Check if all checkboxes in the body are now checked
                if ($('.contact-list-checkbox:checked').length === $('.contact-list-checkbox').length) {
                    $('#select-all-contacts').prop('checked', true);
                }
            }
        });

        // --- Helper Function ---
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return str;
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }


        // --- Action Functions (Global Scope for onclick) ---

        // Remove selected contacts *from this list* (updates the JSON)
        window.removeSelectedFromList = function(listId) {
            const selectedContactIds = $('.contact-list-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedContactIds.length === 0) {
                Swal.fire('No Selection', 'Select contacts to remove from this list.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Confirm Removal',
                text: `Remove ${selectedContactIds.length} contact(s) from this list? (Contacts are not deleted).`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX call to update script (created in Step 5)
                    $.ajax({
                        url: 'ajax_update_contact_list.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'remove_contacts',
                            list_id: listId,
                            contact_ids_to_remove: selectedContactIds
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Removed!', 'Contacts removed from list.', 'success');
                                // Remove rows from table locally or reload page
                                selectedContactIds.forEach(id => {
                                    $(`.contact-row-item[data-contact-id="${id}"]`).fadeOut(400, function() {
                                        $(this).remove();
                                    });
                                });
                                // Update count display
                                const currentCount = parseInt($('#contact-count-display').text()) || 0;
                                $('#contact-count-display').text(currentCount - selectedContactIds.length);
                                $('#select-all-contacts').prop('checked', false);
                            } else {
                                Swal.fire('Error', response.message || 'Failed to update list.', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Server communication error.', 'error');
                            console.error("AJAX Error removing contacts from list:", status, error, xhr.responseText);
                        }
                    });
                }
            });
        }

        // Add Tags (Placeholder - Requires Implementation)
        window.addTagsToSelected = function() {
            const selectedContactIds = $('.contact-list-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedContactIds.length === 0) {
                Swal.fire('No Selection', 'Select contacts to add tags to.', 'warning');
                return;
            }
            // TODO: Implement tag selection (e.g., modal with Select2)
            // TODO: Implement AJAX call to backend (e.g., ajax_manage_tags.php)
            //       Backend needs to loop through contact IDs and add the selected tags.
            Swal.fire('Add Tags', `[Dev] Trigger modal/logic to add tags to contacts: ${selectedContactIds.join(', ')}`, 'info');
        }

        // Export Selected (Uses same logic as search results export)
        window.exportSelectedListContacts = function() {
            const selectedContactIds = $('.contact-list-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedContactIds.length === 0) {
                Swal.fire('No Selection', 'Select contacts to export.', 'warning');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'exportContacts.php'; // Re-use existing export script
            form.style.display = 'none';
            selectedContactIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_contact_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            Swal.fire({
                title: 'Exporting',
                text: 'Download should start shortly.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false
            });
            $('#select-all-contacts').prop('checked', false);
            $('.contact-list-checkbox').prop('checked', false);
        }

        // Create Campaign (Placeholder - Requires Implementation)
        window.createCampaignFromSelected = function() {
            const selectedContactIds = $('.contact-list-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedContactIds.length === 0) {
                Swal.fire('No Selection', 'Select contacts for the campaign.', 'warning');
                return;
            }
            // TODO: Redirect to campaign creation page, passing IDs, or trigger campaign modal.
            Swal.fire('Create Campaign', `[Dev] Trigger campaign logic for contacts: ${selectedContactIds.join(', ')}`, 'info');
        }
    });
</script>