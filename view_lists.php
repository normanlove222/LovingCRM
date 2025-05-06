<?php
// view_lists.php
// Displays lists saved by specific contact selections (from lists_of_contacts table)
require_once 'functions.php'; // Or init.php if that contains required functions/db
require_once 'init.php';     // Make sure $pdo is available if not in functions.php

include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Saved Contact Lists</h1>
    <p>These lists were created by selecting specific contacts.</p>

    <div class="button-group mt-4 mb-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="listManagementActions" data-bs-toggle="dropdown" aria-expanded="false">
                List Management
            </button>
            <ul class="dropdown-menu" aria-labelledby="listManagementActions">
                <li><button class="dropdown-item" onclick="deleteSelectedLists()" type="button">Delete Selected Lists</button></li>
                <!-- Add other actions relevant to managing THESE lists (e.g., Rename) -->
                 <li><hr class="dropdown-divider"></li>
                 <li><a class="dropdown-item" href="view_tag_lists.php">View Tag-Based Lists</a></li> <!-- Link to view the other list type -->

            </ul>
        </div>
    </div>
    <div id="list-count" class="text-muted mb-2">Loading...</div>

    <!-- Display Lists Table -->
    <div id="lists-table-container">
        <table class="table table-hover searchTable"> <!-- Added bootstrap table class -->
            <thead>
                <tr>
                    <th style="width: 5%;"><input type="checkbox" id="select-all-lists" title="Select/Deselect All"></th>
                    <th style="width: 10%;">ID</th>
                    <th>List Name</th>
                    <!-- Removed Tags column -->
                    <th style="width: 20%;">Created</th> <!-- Optional: Add created date? -->
                </tr>
            </thead>
            <tbody id="lists-body">
                <!-- Rows populated by JavaScript -->
                <tr><td colspan="4" class="text-center">Loading lists...</td></tr>
            </tbody>
        </table>
    </div>

     <!-- Actions Dropdown (Optional - for actions on selected lists in *this* table) -->
     <!-- If keeping, adjust options. Add Tag/Remove Tag doesn't apply here -->
    <!--
    <div class="actions mt-3">
        <label for="list-action-select">Actions for selected lists:</label>
        <select id="list-action-select" class="form-select d-inline-block w-auto">
            <option value="">--Select Action--</option>
            <option value="delete">Delete Selected</option>
             <option value="rename">Rename Selected (implement logic)</option>
        </select>
        <button id="apply-list-action" class="btn btn-secondary btn-sm ms-2" onclick="applyListAction()">Apply</button>
    </div>
    -->

</div> <!-- /container -->

<?php
include 'footer.php';
include 'scripts.php'; // Ensure jQuery, Bootstrap JS are included here or in footer
?>

<script>
$(document).ready(function() {
    loadContactLists(); // Call the function to load lists from the new table

    // Select All Checkbox for lists
    $('#select-all-lists').on('change', function() {
        $('.list-checkbox').prop('checked', this.checked);
    });

    // Handle individual checkbox changes to potentially uncheck select-all
    $('#lists-body').on('change', '.list-checkbox', function() {
        if (!this.checked) {
            $('#select-all-lists').prop('checked', false);
        } else {
            // Check if all are checked
            if ($('.list-checkbox:checked').length === $('.list-checkbox').length) {
                $('#select-all-lists').prop('checked', true);
            }
        }
    });

    // --- Function to load lists from lists_of_contacts ---
    function loadContactLists() {
        const listBody = $('#lists-body');
        const listCount = $('#list-count');
        listBody.html('<tr><td colspan="4" class="text-center">Loading lists...</td></tr>'); // Show loading state
        listCount.text('Loading...');

        $.ajax({
            url: 'get_all_contact_lists.php', // *** Use the NEW endpoint ***
            type: 'GET',
            dataType: 'json', // Expect JSON response
            success: function(data) {
                let html = '';
                if (data && !data.error && Array.isArray(data) && data.length > 0) {
                    data.forEach(list => {
                        // *** Link points to the NEW display page ***
                        // *** Removed tags column data ***
                        html += `
                            <tr>
                                <td><input type="checkbox" class="list-checkbox form-check-input" value="${htmlspecialchars(list.list_id)}"></td>
                                <td>${htmlspecialchars(list.list_id)}</td>
                                <td><a href="displayed_lists_of_contacts.php?list_id=${htmlspecialchars(list.list_id)}">${htmlspecialchars(list.list_name)}</a></td>
                                <td>${list.created_at ? new Date(list.created_at).toLocaleDateString() : 'N/A'}</td> <!-- Display created_at if fetched -->
                            </tr>
                        `;
                    });
                    listCount.text(`${data.length} Lists Found`);
                } else if (data.error) {
                     html = `<tr><td colspan="4" class="text-center text-danger">Error loading lists: ${htmlspecialchars(data.error)}</td></tr>`;
                     listCount.text('Error loading lists');
                     console.error("Error loading lists:", data.error);
                }
                else {
                    html = '<tr><td colspan="4" class="text-center">No saved contact lists found.</td></tr>';
                    listCount.text('0 Lists Found');
                }
                listBody.html(html);
            },
            error: function(xhr, status, error) {
                listBody.html('<tr><td colspan="4" class="text-center text-danger">Error loading lists. Check console.</td></tr>');
                listCount.text('Error loading lists');
                console.error("Error loading contact lists:", status, error, xhr.responseText);
            }
        });
    }

    // --- Delete Selected Lists Functionality ---
    window.deleteSelectedLists = function() { // Make it global for onclick
        const selectedListIds = [];
        $('.list-checkbox:checked').each(function() {
            selectedListIds.push($(this).val());
        });

        if (selectedListIds.length === 0) {
            Swal.fire('No Selection', 'Please select at least one list to delete.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedListIds.length} list(s). This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX call to a new backend script to delete from lists_of_contacts
                $.ajax({
                    url: 'ajax_delete_contact_lists.php', // *** NEED TO CREATE THIS SCRIPT ***
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        list_ids: selectedListIds
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                'The selected lists have been deleted.',
                                'success'
                            );
                            loadContactLists(); // Reload the list table
                            $('#select-all-lists').prop('checked', false); // Uncheck select all
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete lists.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Could not communicate with the server to delete lists.', 'error');
                        console.error("Error deleting lists:", status, error, xhr.responseText);
                    }
                });
            }
        });
    }

    // Basic htmlspecialchars equivalent for JS
    
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return str.replace(/[&<>"']/g, m => map[m]);
    }
    // Note: Removed the original .save-list click handler as it's not relevant here.
    // Note: Removed the original applyAction function for tags, as it's not relevant here.
    // If you keep the "Actions for selected lists" dropdown, implement applyListAction().
});
</script>