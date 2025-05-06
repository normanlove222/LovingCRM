<?php
// view_contact_lists.php
// Displays lists saved by specific contact selections (from lists_of_contacts table)
require_once 'functions.php'; // Or init.php if it contains required functions/db
require_once 'init.php';     // Make sure $pdo is available if not in functions.php

include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Saved Contact Lists</h1>
    <p class="text-muted">These lists were created by selecting specific contacts.</p>

    <div class="button-group mt-4 mb-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="listManagementActions" data-bs-toggle="dropdown" aria-expanded="false">
                List Management
            </button>
            <ul class="dropdown-menu" aria-labelledby="listManagementActions">
                <li><button class="dropdown-item" onclick="deleteSelectedLists()" type="button">Delete Selected Lists</button></li>
                <!-- Add other actions like Rename if needed later -->
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="view_tag_lists.php">View Tag-Based Lists</a></li> <!-- Adjust href if your tag list viewer has a different name -->
            </ul>
        </div>
    </div>
    <div id="list-count" class="text-muted mb-2">Loading...</div>

    <!-- Display Lists Table -->
    <div id="lists-table-container" class="table-responsive">
        <table class="table table-hover searchTable"> <!-- Added bootstrap classes -->
            <thead>
                <tr>
                    <th style="width: 5%;"><input type="checkbox" class="form-check-input" id="select-all-lists" title="Select/Deselect All"></th>
                    <th style="width: 10%;">ID</th>
                    <th>List Name</th>
                    <th style="width: 20%;">Created</th>
                </tr>
            </thead>
            <tbody id="lists-body">
                <!-- Rows populated by JavaScript -->
                <tr>
                    <td colspan="4" class="text-center">Loading lists...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div> <!-- /container -->

<?php
include 'footer.php';
include 'scripts.php'; // Ensure jQuery, Bootstrap JS, SweetAlert are included here or in footer
?>
<script>
    $(document).ready(function() {
                loadContactLists(); // Initial load

                // --- Event Listeners ---
                // Select All Checkbox for lists
                $('#select-all-lists').on('change', function() {
                    $('.list-checkbox').prop('checked', this.checked);
                });

                // Individual list checkbox change
                $('#lists-body').on('change', '.list-checkbox', function() {
                    if (!this.checked) {
                        $('#select-all-lists').prop('checked', false);
                    } else {
                        if ($('.list-checkbox:checked').length === $('.list-checkbox').length) {
                            $('#select-all-lists').prop('checked', true);
                        }
                    }
                });

                // --- Core Function to Load Lists ---
                function loadContactLists() {
                    const listBody = $('#lists-body');
                    const listCount = $('#list-count');
                    listBody.html('<tr><td colspan="4" class="text-center">Loading lists...</td></tr>');
                    listCount.text('Loading...');
                    $('#select-all-lists').prop('checked', false); // Ensure select-all is unchecked on load

                    $.ajax({
                        url: 'get_all_contact_lists.php', // Calls the script from Step 1
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let html = '';
                            if (data && !data.error && Array.isArray(data) && data.length > 0) {
                                data.forEach(list => {
                                    const listId = htmlspecialchars(list.list_id);
                                    const listName = htmlspecialchars(list.list_name);
                                    // Format date nicely, handle nulls
                                    const createdDate = list.created_at ? new Date(list.created_at).toLocaleDateString() : 'N/A';
                                    html += `
                            <tr>
                                <td><input type="checkbox" class="list-checkbox form-check-input" value="${listId}"></td>
                                <td>${listId}</td>
                                <td><a href="display_list_of_contacts.php?list_id=${listId}">${listName}</a></td>
                                <td>${createdDate}</td>
                            </tr>
                        `;
                                });
                                listCount.text(`${data.length} List(s) Found`);
                            } else if (data && data.error) {
                                html = `<tr><td colspan="4" class="text-center text-danger">Error: ${htmlspecialchars(data.error)}</td></tr>`;
                                listCount.text('Error loading lists');
                                console.error("Error loading lists:", data.error);
                            } else {
                                html = '<tr><td colspan="4" class="text-center">No saved contact lists found.</td></tr>';
                                listCount.text('0 Lists Found');
                            }
                            listBody.html(html);
                        },
                        error: function(xhr, status, error) {
                            listBody.html('<tr><td colspan="4" class="text-center text-danger">Error contacting server. Check console.</td></tr>');
                            listCount.text('Error loading lists');
                            console.error("AJAX Error loading contact lists:", status, error, xhr.responseText);
                        }
                    });
                }

                // --- Delete Selected Lists ---
                // Make function global for the onclick attribute
                window.deleteSelectedLists = function() {
                    const selectedListIds = $('.list-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get(); // Get array of values

                    if (selectedListIds.length === 0) {
                        Swal.fire('No Selection', 'Please select at least one list to delete.', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm Deletion',
                        text: `Delete ${selectedListIds.length} list(s)? This cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX call to delete script (created in Step 4)
                            $.ajax({
                                url: 'ajax_delete_contact_lists.php',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    list_ids: selectedListIds
                                }, // Send IDs as an array
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Deleted!', 'Selected lists have been deleted.', 'success');
                                        loadContactLists(); // Reload the table
                                    } else {
                                        Swal.fire('Error', response.message || 'Failed to delete lists.', 'error');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire('Error', 'Server communication error during delete.', 'error');
                                    console.error("AJAX Error deleting lists:", status, error, xhr.responseText);
                                }
                            });
                        }
                    });
                }

                // --- Helper Function ---
                
                function htmlspecialchars(str) {
                    if (typeof str !== 'string') return str;
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    };
                    return str.replace(/[&<>"']/g, m => map[m]);
                }
                
});
</script>