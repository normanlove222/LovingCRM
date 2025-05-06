<?php
//this is the page to manage our saved lists. it displays all our lists by name from lists table
require_once 'functions.php';

include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Saved Tag Lists</h1>
    <p class="text-muted">These lists were created using tag selections.</p>

    <div class="button-group mt-4 mb-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="listManagementActions" data-bs-toggle="dropdown" aria-expanded="false">
                List Management
            </button>
            <ul class="dropdown-menu" aria-labelledby="listManagementActions">
                <li><button class="dropdown-item" onclick="deleteSelectedLists()" type="button">Delete Selected Lists</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="view_contacts_lists.php">View Contact-Based Lists</a></li>
            </ul>
        </div>
    </div>
    <div id="list-count" class="text-muted mb-2">Loading...</div>

    <!-- Display Lists Table -->
    <div id="lists-table-container" class="table-responsive">
        <table class="table table-hover searchTable">
            <thead>
                <tr>
                    <th style="width: 5%;"><input type="checkbox" class="form-check-input" id="select-all-lists" title="Select/Deselect All"></th>
                    <th style="width: 10%;">ID</th>
                    <th>List Name</th>
                    <th>Tags</th>
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

</div> <!-- container div-->

<?php
include 'footer.php';
include 'scripts.php';
?>

<script>
    // Define loadTagLists as a global function
    let loadTagLists;
    
    $(document).ready(function() {
        // --- Core Function to Load Lists ---
        loadTagLists = function() {
            const listBody = $('#lists-body');
            const listCount = $('#list-count');
            listBody.html('<tr><td colspan="4" class="text-center">Loading lists...</td></tr>');
            listCount.text('Loading...');
            $('#select-all-lists').prop('checked', false); // Ensure select-all is unchecked on load

            $.ajax({
                url: 'get_all_lists.php', // Calls the existing script
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (data && !data.error && Array.isArray(data) && data.length > 0) {
                        data.forEach(list => {
                            const listId = htmlspecialchars(list.list_id);
                            const listName = htmlspecialchars(list.list_name);
                            const tagNames = htmlspecialchars(list.tag_names || '');
                            html += `
                            <tr>
                                <td><input type="checkbox" class="list-checkbox form-check-input" value="${listId}"></td>
                                <td>${listId}</td>
                                <td><a href="open_list.php?list_id=${listId}">${listName}</a></td>
                                <td>${tagNames}</td>
                            </tr>
                        `;
                        });
                        listCount.text(`${data.length} List(s) Found`);
                    } else if (data && data.error) {
                        html = `<tr><td colspan="4" class="text-center text-danger">Error: ${htmlspecialchars(data.error)}</td></tr>`;
                        listCount.text('Error loading lists');
                        console.error("Error loading lists:", data.error);
                    } else {
                        html = '<tr><td colspan="4" class="text-center">No saved tag lists found.</td></tr>';
                        listCount.text('0 Lists Found');
                    }
                    listBody.html(html);
                },
                error: function(xhr, status, error) {
                    listBody.html('<tr><td colspan="4" class="text-center text-danger">Error contacting server. Check console.</td></tr>');
                    listCount.text('Error loading lists');
                    console.error("AJAX Error loading tag lists:", status, error, xhr.responseText);
                }
            });
        };

        // Call loadTagLists initially
        loadTagLists();

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
            text: `Delete ${selectedListIds.length} tag list(s)? This cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX call to delete script
                $.ajax({
                    url: 'ajax_delete_tag_lists.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        list_ids: selectedListIds
                    }, // Send IDs as an array
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'Selected lists have been deleted.', 'success');
                            // Reload the table
                            loadTagLists();
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
    
    // Make htmlspecialchars globally available
    window.htmlspecialchars = function(str) {
        if (typeof str !== 'string') return str;
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return str.replace(/[&<>"']/g, m => map[m]);
    };
</script>