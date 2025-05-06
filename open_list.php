<?php
session_start();  
require_once('c1.php');  

require_once 'functions.php';
require_once('require_login.php');
include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <h1>Open Saved List</h1>
    <p>Here you can run processes on your opened list, like Print, export to .csv, or send a campaign to this list.</p>

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
                <li><button id="printContacts" class="dropdown-item" type="button">Print</button></li>
                <li><button id="exportCSV" class="dropdown-item" type="button">Export Contacts</button></li>
                <li>-----------------------</li>
                <!-- <li><button class="dropdown-item" type="button">Create A Campaign</button></li> -->
                <!-- <li><button class="dropdown-item" type="button">Merge Duplicates</button></li> -->
                <!-- <li><button class="dropdown-item" type="button">Send Broadcast</button></li> -->
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
        // Function to get query parameter by name
        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Get list_id from URL
        const listId = getQueryParam('list_id');

        if (!listId) {
            console.error("List ID is missing in the URL");
            return;
        }

        // Initialize Select2 for tags
        $('#tags').select2({
            placeholder: "Select tags",
            allowClear: true,
            multiple: true,
            tags: true
        });

        // Load tags from the saved list
        $.ajax({
            url: 'fetch_saved_list_tags.php',
            type: 'GET',
            data: {
                list_id: listId
            },
            dataType: 'json',
            success: function(data) {
                // Clear any existing options
                $('#tags').empty();

                // Add tags to select
                data.forEach(function(tag) {
                    var option = new Option(tag.text, tag.id, true, true); // Set selected to true
                    $('#tags').append(option);
                });

                // Trigger change to update Select2
                $('#tags').trigger('change');
            },
            error: function(error) {
                console.error("Error loading tags:", error);
            }
        });

        // Handle tag selection change
        $('#tags').on('change', function() {
            fetchContacts();
        });

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

        // Handle "Print" action
        $('#printContacts').on('click', function() {
            let selectedTags = $('#tags').val();
            let selectedContacts = [];
            $('input.contact-checkbox:checked').each(function() {
                selectedContacts.push($(this).val());
            });

            // Fetch contact details for selected contacts
            $.ajax({
                url: 'fetch_contacts.php',
                type: 'POST',
                data: {
                    tags: selectedTags
                },
                success: function(data) {
                    // console.log(data); // Log the data to check its format

                    // Create a structured layout using Flexbox
                    let contactDetails = `
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; font-weight: bold;">
                                    <div style="flex: 1;">First Name</div>
                                    <div style="flex: 1;">Last Name</div>
                                    <div style="flex: 2;">Email</div>
                                </div>
                                ${data.map(contact => `
                                    <div style="display: flex;">
                                        <div style="flex: 1;">${contact.first_name}</div>
                                        <div style="flex: 1;">${contact.last_name}</div>
                                        <div style="flex: 2;">${contact.email}</div>
                                    </div>
                                `).join('')}
                            </div>
                        `;

                    // Show SweetAlert with contact details
                    Swal.fire({
                        title: 'Contact Details',
                        html: `
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <button id="print-button" class="swal2-confirm swal2-styled" style="background-color: #3085d6; color: white;">Print</button>
                                    <button class="swal2-confirm swal2-styled" style="background-color: #3085d6; color: white;" onclick="Swal.close()">OK</button>
                                </div>
                                ${contactDetails}
                            `,
                        showConfirmButton: false,
                        width: '600px' // Increase the width of the dialog
                    });

                    // Add print functionality
                    $('#print-button').on('click', function() {
                        let printWindow = window.open('', '_blank');
                        printWindow.document.write('<html><head><title>Print Contacts</title></head><body>');
                        printWindow.document.write(contactDetails);
                        printWindow.document.write('</body></html>');
                        printWindow.document.close();
                        printWindow.print();
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching contact details:", error);
                }
            });
        });

        // Handle "Export Contacts" action
        $('#exportCSV').on('click', function() {
            let selectedTags = $('#tags').val();

            // Fetch contacts based on selected tags
            $.ajax({
                url: 'fetch_contacts.php',
                type: 'POST',
                data: {
                    tags: selectedTags
                },
                success: function(data) {
                    // Convert data to CSV format
                    let csvContent = "data:text/csv;charset=utf-8,";
                    csvContent += "First Name,Last Name,Email,State\n"; // Header row

                    data.forEach(contact => {
                        let row = `${contact.first_name},${contact.last_name},${contact.email},${contact.state}`;
                        csvContent += row + "\n";
                    });

                    // Create a download link and trigger it
                    var encodedUri = encodeURI(csvContent);
                    var link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", "contacts.csv");
                    document.body.appendChild(link); // Required for FF

                    link.click(); // This will download the data file named "contacts.csv"
                    document.body.removeChild(link); // Clean up
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching contacts for export:", error);
                }
            });
        });


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