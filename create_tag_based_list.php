<?php
require_once('init.php');
include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Saved Lists</h1>
    <p class="alert alert-info">Search for tags to build your tag based list. Then you can save or export or print the list. Saved lists can be used when emailing.</p>
    <!-- Criteria Selection Area -->
    <div class="criteria-selection">
        <label for="tags">Select Tags:</label>
        <select id="tags" multiple="multiple" style="width: 300px;">
            <!-- Options populated by JavaScript -->
        </select>
    </div>

    <div class="button-group mt-4 mb-3">

        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                List Actions
            </button>
            <ul class="dropdown-menu">
                <li><button class="dropdown-item save-list" type="button">Save List</button></li>
                <li><button class="dropdown-item" type="button">Add Tag</button></li>
                <li><button class="dropdown-item" type="button">Remove Tag</button></li>
                <li><button class="dropdown-item" type="button">Delete Selected</button></li>
                <li><button class="dropdown-item" type="button">Create A Campaign</button></li>
                <li><button class="dropdown-item" type="button">Print</button></li>
                <li><button class="dropdown-item" type="button">Export Contacts</button></li>
                <li><button class="dropdown-item" type="button">Merge Duplicates</button></li>
                <li><button class="dropdown-item" type="button">Send Broadcast</button></li>
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>State</th>

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
    $(document).ready(function() {
        // loadAllContacts();

        // Initialize Select2 for tags with AJAX loading
        $('#tags').select2({
            placeholder: "Select tags",
            allowClear: true,
            multiple: true
        });

        // Load tags immediately on page load
        $.ajax({
            url: 'fetch_tags.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Clear any existing options
                $('#tags').empty();

                // Add tags to select
                data.forEach(function(tag) {
                    var option = new Option(tag.text, tag.id, false, false);
                    $('#tags').append(option);
                });

                // Trigger change to update Select2
                // $('#tags').trigger('change');
            },
            error: function(error) {
                console.error("Error loading tags:", error);
            }
        });


        // Handle tag selection change
        $('#tags').on('change', function() {
            // console.log(tags);
            fetchContacts();
            saveTags();
        });


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
                        return 'You need to enter a list name!';
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

        function loadAllContacts() {
            $.ajax({
                url: 'get_all_contacts.php',
                type: 'GET',
                success: function(data) {

                    let html = '';
                    data.forEach(contact => {
                        html += `
                            <tr>
                                <td><input type="checkbox" class="contact-checkbox" value="${contact.contact_id}"></td>
                                <td>${contact.first_name} ${contact.last_name}</td>
                                <td>${contact.email}</td>
                                <td>${contact.state}</td>
                            </tr>
                        `;
                    });
                    $('#contacts-body').html(html);

                    // Update the contacts count
                    $('.contacts-count').html(`${data.length} Contacts`);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading contacts:", error);
                }
            });
        }

        // Fetch and display contacts based on selected tags
        function fetchContacts() {
            let selectedTags = $('#tags').val(); // This gets array of selected tag IDs
            $.ajax({
                url: 'fetch_contacts.php',
                type: 'POST',
                data: {
                    tags: selectedTags
                },
                success: function(data) {
                    let html = '';
                    data.forEach(contact => {
                        html += `
                            <tr>
                                <td><input type="checkbox" class="contact-checkbox" value="${contact.contact_id}"></td>
                                <td>${contact.first_name} ${contact.last_name}</td>
                                <td>${contact.email}</td>
                                <td>${contact.state}</td>
                            </tr>
                        `;
                    });
                    $('#contacts-body').html(html);
                    $('.contacts-count').html(`Audience is ${data.length} contacts`);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching contacts:", error);
                }
            });
        }

        // Save tags to the database
        function saveTags(isManualSave = false) {
            let selectedTags = $('#tags').val();
            $.ajax({
                url: 'save_list.php',
                type: 'POST',
                data: {
                    tags: selectedTags,
                    manualSave: isManualSave
                },
                success: function(response) {
                    if (isManualSave) alert('List saved!');
                    fetchContacts(); // Refresh contacts based on selected tags
                }
            });
        }


        // Apply action from the dropdown
        $('#apply-action').click(function() {
            let action = $('#action-select').val();
            let selectedContacts = [];
            $('input.contact-checkbox:checked').each(function() {
                selectedContacts.push($(this).val());
            });

            $.ajax({
                url: 'apply_action.php',
                type: 'POST',
                data: {
                    action: action,
                    contacts: selectedContacts,
                    tag: $('#tags').val() // Pass the selected tag for add/remove
                },
                success: function(response) {
                    fetchContacts(); // Refresh contacts after action
                }
            });
        });
    });
</script>

</body>

</html>