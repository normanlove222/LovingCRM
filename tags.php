<?php
require_once 'init.php';

// Initial page load
$records_per_page = 25;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$total_records = getTagsCount($pdo);
$pagination_vars = getPaginationVars($total_records, $records_per_page, $current_page);
$tags = getTagsData($pdo, $pagination_vars, '');

include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h4 mb-4">Tags</h1>

            <div class="row align-items-center mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Name" id="searchInput">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">Search</button>
                    </div>
                </div>

                <!-- <div class="col-md-4">
                    <select class="form-select">
                        <option selected>Show all categories</option>
                    </select>
                </div> -->

                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Add Tag</button>
                    </div>
                </div>

            </div>
            <p>Tags are listed so last added show first(Descending Order)</p>
            <div class="col-md-8 text-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="Delete_selected.php">Delete selected</a></li>
                        <li><a class="dropdown-item" href="#Print">Print</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        echo '<p class="text-muted">' . $total_records . ' results</p>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover" id="tagsTable">';
        echo '<thead>
                    <tr>
                        <th width="30"><input type="checkbox" class="form-check-input" id="selectAllCheckbox"></th>
                        <th>Id</th>
                        <th>Name</th>                        
                        <th>Number of people</th>
                        <th>Contacts</th>
                        
                    </tr>
                </thead>';
        echo '<tbody>';

        foreach ($tags as $tag) {
            echo '<tr>';
            echo '<td><input type="checkbox" class="form-check-input tag-checkbox" data-tag-id="' . $tag['tag_id'] . '"></td>';
            echo '<td>' . htmlspecialchars($tag['tag_id']) . '</td>';
            echo '<td>' . htmlspecialchars($tag['name']) . '</td>';
            // echo '<td>' . htmlspecialchars($tag['category'] ?? '') . '</td>';
            echo '<td><button class="show-number-btn">Show Number</button></td>';
            echo '<td><button class="show-contacts-btn">contacts</button></td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';

        if ($pagination_vars['total_pages'] > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination_vars['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($pagination_vars['current_page'] - 1); ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination_vars['total_pages']; $i++): ?>
                        <li class="page-item <?php echo ($pagination_vars['current_page'] == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination_vars['current_page'] < $pagination_vars['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($pagination_vars['current_page'] + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
include 'footer.php';
include 'scripts.php';
?>

</script>

<!-- Contact Details Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="contactModalClose"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p id="contactEmail" class="mb-2"></p>
                        <p id="contactPhone" class="mb-2"></p>
                        <p id="contactAddress" class="mb-2"></p>
                        <p id="contactLocation" class="mb-2"></p>
                        <p id="contactCompany" class="mb-2"></p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" id="tagsBtn">
                                <i class="bi bi-tag-fill"></i> Tags
                            </button>
                            <button class="btn btn-outline-primary" id="notesBtn">
                                <i class="bi bi-journal-text"></i> Notes
                            </button>
                            <button class="btn btn-outline-primary" id="addTagBtn">
                                <i class="bi bi-plus-lg"></i> Add Tag
                            </button>
                            <button class="btn btn-outline-danger" id="deleteTagsBtn">
                                <i class="bi bi-dash-circle"></i> -Tags
                            </button>
                            <button class="btn btn-outline-secondary" id="editContactBtn">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>
                <div id="tagsContent" class="mt-3 d-none"></div>
                <div id="notesContent" class="mt-3 d-none">
                    <p id="notesText"></p>
                    <a href="#" id="editNotesLink">Edit</a>
                </div>
                <div id="addTagContent" class="mt-3 d-none">
                    <form id="addTagForm">
                        <div class="input-group">
                            <input type="text" class="form-control" id="newTagInput" placeholder="Enter new tag" required>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Notes Modal -->
<div class="modal fade" id="editNotesModal" tabindex="-1" aria-labelledby="editNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNotesModalLabel">Edit Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="editNotesTextarea" class="form-control" rows="5"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNotesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tags Modal -->
<div class="modal fade" id="deleteTagsModal" tabindex="-1" aria-labelledby="deleteTagsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTagsModalLabel">Delete Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="tagsList" class="list-group">
                    <!-- Tags will be dynamically inserted here -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContactForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editPhone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="address">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="editCity" name="city">
                        </div>
                        <div class="col-md-4">
                            <label for="editState" class="form-label">State</label>
                            <input type="text" class="form-control" id="editState" name="state">
                        </div>
                        <div class="col-md-2">
                            <label for="editZipCode" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="editZipCode" name="zip_code">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCompany" class="form-label">Company</label>
                        <input type="text" class="form-control" id="editCompany" name="company">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveContactBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // First, define the function in the global scope
    function printContacts() {
        const tagName = $('.swal2-title').text();
        const contacts = $('.swal2-html-container table tbody tr').map(function() {
            return {
                name: $(this).find('td:eq(0)').text(),
                email: $(this).find('td:eq(1)').text()
            };
        }).get();

        // Create a new window for printing
        const printWindow = window.open('', '_blank');

        // Generate the print content with styling
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Contacts</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .print-date {
                        color: #666;
                        font-size: 0.9em;
                        margin-bottom: 20px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 12px;
                        text-align: left;
                    }
                    th {
                        background-color: #f5f5f5;
                        font-weight: bold;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .footer {
                        margin-top: 20px;
                        text-align: center;
                        font-size: 0.8em;
                        color: #666;
                    }
                    @media print {
                        .no-print {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>${tagName}</h1>
                    <div class="print-date">Printed on: ${new Date().toLocaleString()}</div>
                </div>
                
                <div>Total Contacts: ${contacts.length}</div>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${contacts.map((contact, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${contact.name}</td>
                                <td>${contact.email}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="footer">
                    <p>Generated from Loving CRM - ${new Date().toLocaleDateString()}</p>
                </div>
                
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print();" style="padding: 10px 20px;">Print</button>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
    }

    function printSelectedTags() {
        const selectedTags = $('input.tag-checkbox:checked').map(function() {
            return {
                id: $(this).closest('tr').find('td:eq(1)').text(),
                name: $(this).closest('tr').find('td:eq(2)').text()
            };
        }).get();

        if (selectedTags.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Tags Selected',
                text: 'Please select at least one tag to print.'
            });
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
           <!DOCTYPE html>
           <html>
           <head>
               <title>Print Selected Tags</title>
               <style>
                   body { font-family: Arial, sans-serif; margin: 20px; }
                   table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                   th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                   th { background-color: #f5f5f5; }
                   .header { text-align: center; margin-bottom: 20px; }
                   .footer { margin-top: 20px; text-align: center; font-size: 0.8em; }
                   @media print { .no-print { display: none; } }
               </style>
           </head>
           <body>
               <div class="header">
                   <h1>Selected Tags</h1>
                   <div>Printed on: ${new Date().toLocaleString()}</div>
               </div>
               
               <div>Total Tags: ${selectedTags.length}</div>
               
               <table>
                   <thead>
                       <tr>
                           <th>#</th>
                           <th>ID</th>
                           <th>Name</th>
                       </tr>
                   </thead>
                   <tbody>
                       ${selectedTags.map((tag, index) => `
                           <tr>
                               <td>${index + 1}</td>
                               <td>${tag.id}</td>
                               <td>${tag.name}</td>
                           </tr>
                       `).join('')}
                   </tbody>
               </table>
               
               <div class="footer">
                   <p>Generated from Loving CRM - ${new Date().toLocaleDateString()}</p>
               </div>
               
               <div class="no-print" style="text-align: center; margin-top: 20px;">
                   <button onclick="window.print();" style="padding: 10px 20px;">Print</button>
               </div>
           </body>
           </html>
       `);
        printWindow.document.close();
    }

    // Modal functionality - single instances of each modal
    let contactModal;
    let editNotesModal;
    let deleteTagsModal;
    let editContactModal;
    let currentContactId;

    // Stack to keep track of modals (needed for closeAllModals function)
    const modalStack = [];

    function initializeModals() {
        // Initialize modal objects with simple default settings
        contactModal = new bootstrap.Modal(document.getElementById('contactModal'), {
            backdrop: true, // Allow clicking outside to close
            keyboard: true // Allow ESC key to close
        });

        editNotesModal = new bootstrap.Modal(document.getElementById('editNotesModal'), {
            backdrop: true,
            keyboard: true
        });

        deleteTagsModal = new bootstrap.Modal(document.getElementById('deleteTagsModal'), {
            backdrop: true,
            keyboard: true
        });

        editContactModal = new bootstrap.Modal(document.getElementById('editContactModal'), {
            backdrop: true,
            keyboard: true
        });

        // Add cleanup and parent-child modal relationship handling to modal hidden events
        document.getElementById('contactModal').addEventListener('hidden.bs.modal', function() {
            cleanupModalBackdrops();

            // If we have the contacts data in the window global variables, reopen the contacts list
            // But only if we're not in one of the modal flows
            if (window.currentTagName && window.currentTagContacts &&
                !window.inDeleteTagsFlow &&
                !window.inEditNotesFlow &&
                !window.inEditContactFlow) {
                setTimeout(() => {
                    showContactsList(window.currentTagName, window.currentTagContacts);
                }, 300);
            }
        });

        document.getElementById('editNotesModal').addEventListener('hidden.bs.modal', function() {
            cleanupModalBackdrops();

            // Reset the edit notes flow flag when modal is hidden
            window.inEditNotesFlow = false;

            // If there's a parent modal in stack, reopen it
            setTimeout(() => {
                if (modalStack.length > 0) {
                    const parentModal = modalStack.pop();
                    if (parentModal === 'contactModal' && contactModal) {
                        contactModal.show();
                    }
                }
            }, 300);
        });

        document.getElementById('deleteTagsModal').addEventListener('hidden.bs.modal', function() {
            cleanupModalBackdrops();

            // Reset the delete tags flow flag when modal is hidden
            window.inDeleteTagsFlow = false;

            // If there's a parent modal in stack, reopen it
            setTimeout(() => {
                if (modalStack.length > 0) {
                    const parentModal = modalStack.pop();
                    if (parentModal === 'contactModal' && contactModal) {
                        contactModal.show();
                    }
                }
            }, 300);
        });

        document.getElementById('editContactModal').addEventListener('hidden.bs.modal', function() {
            cleanupModalBackdrops();

            // Reset the edit contact flow flag when modal is hidden
            window.inEditContactFlow = false;

            // If there's a parent modal in stack, reopen it
            setTimeout(() => {
                if (modalStack.length > 0) {
                    const parentModal = modalStack.pop();
                    if (parentModal === 'contactModal' && contactModal) {
                        contactModal.show();
                    }
                }
            }, 300);
        });
    }

    // Function to ensure only one modal backdrop exists
    function cleanupModalBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            // Keep only the last backdrop
            for (let i = 0; i < backdrops.length - 1; i++) {
                backdrops[i].remove();
            }
        }
    }

    // Function to close all open modals
    function closeAllModals() {
        // Reset all flow flags
        window.inDeleteTagsFlow = false;
        window.inEditNotesFlow = false;
        window.inEditContactFlow = false;

        // Close modals in reverse order of their typical nesting
        if (editNotesModal) {
            editNotesModal.hide();
        }

        if (deleteTagsModal) {
            deleteTagsModal.hide();
        }

        if (editContactModal) {
            editContactModal.hide();
        }

        // Close contact detail modal last if it exists
        if (contactModal) {
            contactModal.hide();
        }

        // Short timeout to ensure modals have time to trigger their hide events
        setTimeout(() => {
            // Remove any remaining backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Fix the body class
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 300);

        // Clear the modal stack
        modalStack.length = 0;
    }

    // Create a function to show the contacts list
    function showContactsList(tagName, contacts) {
        Swal.fire({
            title: `Contacts for tag: ${tagName}`,
            html: `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${contacts.map(contact => `
                            <tr>
                                <td><a href="#" class="contact-link" data-contact-id="${contact.contact_id}">${contact.first_name} ${contact.last_name}</a></td>
                                <td>${contact.email}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="mt-3">
                    <button id="exportCSV" class="btn btn-primary">Export CSV</button>
                    <button class="btn btn-secondary" onclick="printContacts()">Print</button>
                </div>
            `,
            confirmButtonText: 'Close',
            showConfirmButton: false,
            width: '800px',
            didOpen: () => {
                // When SweetAlert opens, attach event listeners to contact links
                document.querySelectorAll('.contact-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const contactId = this.dataset.contactId;

                        // Close the SweetAlert first
                        Swal.close();

                        // Then show contact details
                        setTimeout(() => {
                            showContactDetails(contactId);
                        }, 300);
                    });
                });

                // Add export CSV handler
                const exportBtn = document.getElementById('exportCSV');
                if (exportBtn) {
                    exportBtn.addEventListener('click', function() {
                        $.ajax({
                            url: 'export_contacts.php',
                            method: 'POST',
                            data: {
                                tagId: window.currentTagId,
                                tagName: tagName
                            },
                            success: function(response) {
                                try {
                                    response = typeof response === 'string' ? JSON.parse(response) : response;

                                    if (response.error) {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Failed to export: ' + response.error,
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                        return;
                                    }

                                    var blob = new Blob([response.data], {
                                        type: 'text/csv'
                                    });
                                    var link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download = tagName + '.csv';
                                    link.click();

                                    Swal.fire({
                                        title: 'Success!',
                                        text: tagName + '.csv has been downloaded',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Show contacts list again
                                        showContactsList(tagName, contacts);
                                    });
                                } catch (e) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Failed to process export: ' + e.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to export: ' + error,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    });
                }
            }
        });
    }

    function showContactDetails(contactId) {
        // If SweetAlert is visible, store its content before closing it
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            // Store the SweetAlert content for reopening later
            const swalContainer = document.querySelector('.swal2-html-container');
            if (swalContainer) {
                lastSwalContent = swalContainer.innerHTML;

                // Try to get the tag name from the title
                const titleElement = document.querySelector('.swal2-title');
                if (titleElement) {
                    lastTagName = titleElement.textContent.replace('Contacts for tag: ', '');
                }

                // Try to get the contacts data
                try {
                    const rows = document.querySelectorAll('.swal2-html-container table tbody tr');
                    lastContacts = Array.from(rows).map(row => {
                        const linkElement = row.querySelector('.contact-link');
                        const emailElement = row.querySelector('td:nth-child(2)');
                        return {
                            contact_id: linkElement ? linkElement.dataset.contactId : '',
                            first_name: linkElement ? linkElement.textContent.split(' ')[0] : '',
                            last_name: linkElement ? linkElement.textContent.split(' ').slice(1).join(' ') : '',
                            email: emailElement ? emailElement.textContent : ''
                        };
                    });
                } catch (e) {
                    console.error('Error storing contacts data:', e);
                }
            }

            // Now close the SweetAlert
            Swal.close();
        }

        fetch(`get_contact_details.php?contact_id=${contactId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                currentContactId = contactId;
                document.getElementById('contactName').textContent = `${data.first_name} ${data.last_name}`;
                document.getElementById('contactEmail').textContent = data.email;
                document.getElementById('contactPhone').textContent = data.phone;
                document.getElementById('contactAddress').textContent = data.address;
                document.getElementById('contactLocation').textContent = `${data.city}, ${data.state} ${data.zip_code}`;
                document.getElementById('contactCompany').textContent = data.company;

                updateTagsDisplay(data.tags);
                document.getElementById('notesText').textContent = data.notes || 'No notes available';

                // Show the contact modal (standard Bootstrap behavior)
                contactModal.show();

                // Clean up any duplicate backdrops
                cleanupModalBackdrops();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to load contact details: ${error.message || 'Unknown error'}`
                });
            });
    }

    function showEditContactModal() {
        // Flag to prevent automatic reopening of contacts list
        window.inEditContactFlow = true;

        // Store the parent modal in the modalStack
        modalStack.push('contactModal');

        fetch(`get_contact_details.php?contact_id=${currentContactId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                document.getElementById('editFirstName').value = data.first_name;
                document.getElementById('editLastName').value = data.last_name;
                document.getElementById('editEmail').value = data.email;
                document.getElementById('editPhone').value = data.phone;
                document.getElementById('editAddress').value = data.address;
                document.getElementById('editCity').value = data.city;
                document.getElementById('editState').value = data.state;
                document.getElementById('editZipCode').value = data.zip_code;
                document.getElementById('editCompany').value = data.company;

                // Close contact modal and show edit modal
                contactModal.hide();
                editContactModal.show();

                // Clean up backdrops
                cleanupModalBackdrops();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load contact details: ' + error.message || 'Unknown error'
                });

                // Reset the flag on error
                window.inEditContactFlow = false;
            });
    }

    function showDeleteTagsModal() {
        // Flag to prevent automatic reopening of contacts list
        window.inDeleteTagsFlow = true;

        // Store the parent modal in the modalStack before showing child modal
        modalStack.push('contactModal');

        fetch(`get_delete_tags.php?contact_id=${currentContactId}`)
            .then(response => response.json())
            .then(tags => {
                const tagsList = document.getElementById('tagsList');
                tagsList.innerHTML = '';

                if (tags.length === 0) {
                    tagsList.textContent = 'No tags available';
                } else {
                    tags.forEach(tag => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = `
                            ${tag.name}
                            <button class="btn btn-danger btn-sm delete-tag" data-tag-id="${tag.tag_id}">
                                <i class="bi bi-dash-circle"></i>
                            </button>
                        `;
                        tagsList.appendChild(li);
                    });
                }

                // Hide contact modal and show delete tags modal
                contactModal.hide();
                deleteTagsModal.show();
                cleanupModalBackdrops();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load tags: ' + error.message || 'Unknown error'
                });
                // Reset the flag on error
                window.inDeleteTagsFlow = false;
            });
    }

    function addTagToContact(contactId, newTag) {
        fetch('add_tag_to_contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `contact_id=${contactId}&tag=${newTag}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                updateTagsDisplay(data.tags);
                document.getElementById('newTagInput').value = '';
                toggleContent('addTagContent');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to add tag: ${error.message || 'Unknown error'}`
                });
            });
    }

    function saveNotes(contactId, newNotes) {
        fetch('update_notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `contact_id=${contactId}&notes=${encodeURIComponent(newNotes)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                document.getElementById('notesText').textContent = newNotes;

                // Reset the edit notes flow flag
                window.inEditNotesFlow = false;

                // Close edit modal and re-show contact modal
                editNotesModal.hide();
                contactModal.show();
                cleanupModalBackdrops();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to save notes: ${error.message || 'Unknown error'}`
                });

                // Reset the flag on error
                window.inEditNotesFlow = false;
            });
    }

    function updateTagsDisplay(tags) {
        const tagsContent = document.getElementById('tagsContent');
        tagsContent.innerHTML = tags.length > 0 ?
            tags.map(tag => `<span class="badge bg-primary me-1">${tag}</span>`).join('') :
            'No tags';
    }

    function toggleContent(contentId) {
        const contentAreas = ['tagsContent', 'notesContent', 'addTagContent'];
        contentAreas.forEach(area => {
            const element = document.getElementById(area);
            if (area === contentId) {
                element.classList.toggle('d-none');
            } else {
                element.classList.add('d-none');
            }
        });
    }

    function updateContact(formData) {
        fetch('update_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                // Reset the edit contact flow flag
                window.inEditContactFlow = false;

                // Hide edit modal
                editContactModal.hide();

                // Show success message and then refresh contact details
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Contact updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Reload contact details
                    showContactDetails(currentContactId);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to update contact: ${error.message || 'Unknown error'}`
                });

                // Reset the flag on error
                window.inEditContactFlow = false;
            });
    }

    function deleteTag(contactId, tagId) {
        fetch('delete_contact_tag.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `contact_id=${contactId}&tag_id=${tagId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                // Find the tag element before removing it
                const tagItem = document.querySelector(`[data-tag-id="${tagId}"]`);
                if (tagItem) {
                    const listItem = tagItem.closest('li');
                    if (listItem) {
                        listItem.remove();
                    }
                }

                // Update the tags display in the main contact details
                updateTagsDisplay(data.tags);

                // Notify user of success with a mini toast that doesn't interrupt workflow
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Tag removed successfully'
                });

                // If no more tags to delete, close this modal and go back to contact modal
                const remainingTags = document.querySelectorAll('#tagsList li');
                if (remainingTags.length === 0) {
                    // Get the parent modal from the stack and re-open it
                    deleteTagsModal.hide();

                    // Reset the delete tags flow flag
                    window.inDeleteTagsFlow = false;

                    // Remove backdrop only after a short delay
                    setTimeout(() => {
                        // Get last modal from stack and show it
                        if (modalStack.length > 0) {
                            const parentModal = modalStack.pop();
                            if (parentModal === 'contactModal' && contactModal) {
                                contactModal.show();
                            }
                        }
                        cleanupModalBackdrops();
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to delete tag: ${error.message || 'Unknown error'}`
                });

                // Reset the delete tags flow flag on error
                window.inDeleteTagsFlow = false;
            });
    }

    // Initialize event handlers for contact modals
    function initializeContactModalHandlers() {
        // Add explicit close handler for contact modal
        document.getElementById('contactModalClose').addEventListener('click', function() {
            closeAllModals(); // Force close all modals
        });

        // Button click events
        document.getElementById('tagsBtn').addEventListener('click', function() {
            toggleContent('tagsContent');
        });

        document.getElementById('notesBtn').addEventListener('click', function() {
            toggleContent('notesContent');
        });

        document.getElementById('addTagBtn').addEventListener('click', function() {
            toggleContent('addTagContent');
        });

        document.getElementById('deleteTagsBtn').addEventListener('click', function() {
            showDeleteTagsModal();
        });

        document.getElementById('editContactBtn').addEventListener('click', function() {
            showEditContactModal();
        });

        // Edit notes link click event
        document.getElementById('editNotesLink').addEventListener('click', function(e) {
            e.preventDefault();

            // Flag to prevent automatic reopening of contacts list
            window.inEditNotesFlow = true;

            // Store the parent modal in the modalStack
            modalStack.push('contactModal');

            const notesText = document.getElementById('notesText').textContent;
            document.getElementById('editNotesTextarea').value = notesText;
            contactModal.hide();
            editNotesModal.show();
            cleanupModalBackdrops();
        });

        // Save notes button click event
        document.getElementById('saveNotesBtn').addEventListener('click', function() {
            const newNotes = document.getElementById('editNotesTextarea').value;
            saveNotes(currentContactId, newNotes);
        });

        // Add Tag form submit event
        document.getElementById('addTagForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newTag = document.getElementById('newTagInput').value;
            addTagToContact(currentContactId, newTag);
        });

        // Save contact button click event
        document.getElementById('saveContactBtn').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('editContactForm'));
            formData.append('contact_id', currentContactId);
            updateContact(formData);
        });

        // Delete tag functionality with improved event delegation
        document.getElementById('tagsList').addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.delete-tag');
            if (deleteButton) {
                e.preventDefault();
                e.stopPropagation();

                const tagId = deleteButton.dataset.tagId;

                // Prevent multiple rapid clicks
                deleteButton.disabled = true;

                // Apply a visual feedback
                deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                // Process the deletion
                deleteTag(currentContactId, tagId);
            }
        });
    }

    // Removed global document click handler for contact links
    // We now attach this directly in the SweetAlert didOpen callback

    $(document).ready(function() {
        // Remove any leftover backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());

        // Remove modal-open class from body if present
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Initialize modals
        initializeModals();
        initializeContactModalHandlers();
        // Replace the performSearch function in tags.php:
        function performSearch(page = 1) {
            var searchTerm = $('#searchInput').val();

            $.ajax({
                url: 'search_tags.php',
                type: 'POST',
                data: {
                    search: searchTerm,
                    page: page,
                    records_per_page: 25
                },
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        $('#tagsTable tbody').html(result.rows);
                        $('.pagination').html(result.pagination);
                        $('.text-muted').html(result.total_records + ' results');
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }


        $('#searchBtn').on('click', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Add Tag button click handler
        $(document).on('click', '.btn-primary:contains("Add Tag")', function() {
            Swal.fire({
                title: 'Enter Tag',
                input: 'text',
                inputPlaceholder: 'Enter tag name',
                showCancelButton: true,
                confirmButtonText: 'Add Tag',
                showLoaderOnConfirm: true,
                preConfirm: (tagName) => {
                    return $.ajax({
                            url: 'add_tag.php',
                            type: 'POST',
                            data: {
                                name: tagName
                            }
                        })
                        .then(response => {
                            try {
                                return typeof response === 'string' ? JSON.parse(response) : response;
                            } catch (error) {
                                throw new Error(response);
                            }
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error.responseText || error.statusText}`
                            );
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Tag added successfully'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: result.value.error || 'Failed to add tag'
                        });
                    }
                }
            });
        });
        // Add this click handler
        $(document).on('click', '.dropdown-item[href="#Print"]', function(e) {
            e.preventDefault();
            printSelectedTags();
        });
        $(document).on('click', '.show-number-btn', function(e) {
            const btn = $(this);
            const tagId = btn.closest('tr').find('td:eq(1)').text(); // Gets the ID from the second column

            $.ajax({
                url: 'get_tag_count.php',
                type: 'GET',
                data: {
                    tag_id: tagId
                },
                success: function(count) {
                    btn.replaceWith(count);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });

        $(document).on('click', '.show-contacts-btn', function(e) {
            e.preventDefault();

            const tagId = $(this).closest('tr').find('td:eq(1)').text();
            const tagName = $(this).closest('tr').find('td:eq(2)').text(); // Get the tag name from the Name column

            // Store the tag info in global variables for reuse
            window.currentTagId = tagId;
            window.currentTagName = tagName;

            $.ajax({
                url: 'get_contacts_by_tag.php',
                type: 'GET',
                data: {
                    tag_id: tagId
                },
                dataType: 'json',
                success: function(response) {
                    const contacts = response;

                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                        return;
                    }

                    if (!contacts || contacts.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Contacts Found',
                            text: 'There are no contacts associated with this tag.'
                        });
                        return;
                    }

                    // Store contacts for later reuse
                    window.currentTagContacts = contacts;

                    showContactsList(tagName, contacts);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch contacts: ' + error
                    });
                }
            });
        });

        // Handle delete selected action
        $(document).on('click', '.dropdown-item[href="Delete_selected.php"]', function(e) {
            e.preventDefault();

            const selectedTags = $('input.tag-checkbox:checked').map(function() {
                return $(this).data('tag-id');
            }).get();

            if (selectedTags.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Tags Selected',
                    text: 'Please select at least one tag to delete.'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Confirm Deletion',
                text: `Are you sure you want to delete these ${selectedTags.length} tags?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, delete them',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_tags.php',
                        type: 'POST',
                        data: {
                            tag_ids: selectedTags
                        },
                        success: function(response) {
                            try {
                                const result = typeof response === 'string' ? JSON.parse(response) : response;
                                if (result.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Tags deleted successfully!'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    throw new Error(result.error || 'Unknown error occurred');
                                }
                            } catch (error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete tags: ' + error
                            });
                        }
                    });
                }
            });
        });
        $(document).on('click', '#exportCSV', function() {
            const tagId = $('.swal2-title').text().match(/tag: (.*)/)[1];
            const tagName = $('.swal2-title').text().replace('Contacts for tag: ', '');
            // console.log('tagId:', tagId);

            $.ajax({
                url: 'export_contacts.php',
                method: 'POST',
                data: {
                    tagId: tagId,
                    tagName: tagName
                },
                success: function(response) {
                    try {
                        response = typeof response === 'string' ? JSON.parse(response) : response;

                        if (response.error) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to export: ' + response.error,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        var blob = new Blob([response.data], {
                            type: 'text/csv'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = tagName + '.csv';
                        link.click();

                        Swal.close();

                        Swal.fire({
                            title: 'Success!',
                            text: tagName + '.csv has been downloaded',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } catch (e) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to process export: ' + e.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to export: ' + error,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Handle select all checkbox
        $(document).on('change', '#selectAllCheckbox', function() {
            $('.tag-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                performSearch();
            }
        });

        $(document).on('click', '.pagination .page-link', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            performSearch(page);
        });
    });
</script>

</body>

</html>