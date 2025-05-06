<?php
//this is the page to manage our saved lists. it displays all our lists by name from lists table
require_once 'functions.php';

include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Saved Lists</h1>

    <div class="button-group mt-4 mb-3">
        <!-- <button id="save-list" class="button">Save</button> -->
        <!-- <button class="button" onclick="refreshList()">New Search</button>
        <button class="button" onclick="editColumns()">Edit Columns</button>
        <button class="button" onclick="printPage()">Print</button>
        <button class="button" onclick="showOptions()">Options</button> -->
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                List Actions
            </button>
            <ul class="dropdown-menu">
                <li><button class="dropdown-item" onclick="#" type="button">Delete Selected</button></li>
                <li><button class="dropdown-item" onclick="#" type="button">Create A Campaign</button></li>
                <li><button class="dropdown-item" onclick="#" type="button">Print</button></li>
                <li><button class="dropdown-item" onclick="#" type="button">Export Contacts</button></li>

            </ul>
        </div>
    </div>
    <div class="contacts-count"></div>
    <!-- Display Contacts Table -->
    <div id="contacts-table">
        <table class="searchTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>ID</th>
                    <th>List name</th>
                    <th>Tags</th>

                </tr>
            </thead>
            <tbody id="contacts-body">
                <!-- Rows populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Actions Dropdown -->
    <div class="actions">
        <select id="action-select">
            <option value="add-tag">Add Tag</option>
            <option value="remove-tag">Remove Tag</option>
        </select>
        <button id="apply-action" onclick="applyAction()">Apply</button>
    </div>

</div> <!-- container div-->
<?php
include 'footer.php';
include 'scripts.php';
?>

<script>
    // Initialize Select2 for tags with AJAX loading
    $(document).ready(function() {
        loadAllLists();

        // Save list button
        $('.save-list').click(function() {
            // Prompt for list name
            Swal.fire({
                title: 'Save List',
                input: 'text',
                inputLabel: 'List Name',
                inputPlaceholder: 'Enter a name for this list',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to enter a list name!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let selectedTags = $('#tags').val();

                    $.ajax({
                        url: 'save_list.php',
                        type: 'POST',
                        data: {
                            list_name: result.value,
                            tags: selectedTags
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                $('<div class="alert alert-success alert-dismissible fade show" role="alert">')
                                    .text('List has been saved successfully!')
                                    .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>')
                                    .insertAfter('h1')
                                    .delay(3000)
                                    .fadeOut();
                            } else {
                                Swal.fire('Error', 'Failed to save list', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to save list', 'error');
                        }
                    });
                }
            });
        });

        function loadAllLists() {
            $.ajax({
                url: 'get_all_lists.php',
                type: 'GET',
                success: function(data) {
                    let html = '';
                    data.forEach(lists => {
                        html += `
                            <tr>
                                <td><input type="checkbox" class="contact-checkbox" value="${lists.list_id}"></td>
                                <td>${lists.list_id}</td>
                                <td><a href="open_list.php?list_id=${lists.list_id}">${lists.list_name}</a></td>
                                <td>${lists.tag_names}</td> <!-- Add this line for tags -->
                            </tr>
                        `;
                    });
                    $('#contacts-body').html(html);

                    // Update the contacts count
                    $('.contacts-count').html(`${data.length} Lists`);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading contacts:", error);
                }
            });
        }




    });
</script>

</body>

</html>