<?php
require_once('init.php');

// Fetch initial contacts (first 25)
$limit = 50;
$offset = 0;
$contacts = getContacts($limit, $offset);
$totalContacts = getTotalContacts();
?>

<?php
include 'header.php';
include 'menu.php';
?>

<div class="container mt-4">
    <div class="row">
        <div id="contactsList" class="col-12 col-lg-4 overflow-auto" style="max-height: 100vh;">
            <h1 class="my-4">All our contacts</h1>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Contact</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody id="contactsListBody">
                        <?php foreach ($contacts as $contact): ?>
                            <tr class="contact-row" data-id="<?= $contact['contact_id'] ?>">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="<?= $contact['contact_id'] ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold fs-5"><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($contact['email']) ?></div>
                                </td>
                                <td class="fs-5">
                                    <?= htmlspecialchars($contact['phone']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="loading" class="text-center d-none">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div id="contactDetails" class="col-12 col-lg-8 d-none overflow-auto" style="max-height: 100vh;">
            <div class="card">
                <div class="card-body">
                    <h2 id="contactName" class="card-title"></h2>
                    <p id="contactEmail" class="card-text"></p>
                    <div class="row mt-4 justify-content-center">
                        <div class="col-auto">
                            <div class="btn-group" role="group" aria-label="Contact actions">
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

    <?php include 'footer.php' ?>
    <?php include 'scripts.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let offset = <?= $limit ?>;
            let loading = false;
            const totalContacts = <?= $totalContacts ?>;
            let currentContactId;

            const editNotesModal = new bootstrap.Modal(document.getElementById('editNotesModal'));
            const deleteTagsModal = new bootstrap.Modal(document.getElementById('deleteTagsModal'));
            const editContactModal = new bootstrap.Modal(document.getElementById('editContactModal'));

            // Lazy loading
            function loadMoreContacts() {
                if (loading) return;
                if (offset >= totalContacts) {
                    document.getElementById('loading').classList.add('d-none');
                    return;
                }

                loading = true;
                document.getElementById('loading').classList.remove('d-none');

                fetch(`get_more_contacts.php?offset=${offset}&limit=${<?= $limit ?>}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim()) {
                            document.getElementById('contactsListBody').insertAdjacentHTML('beforeend', data);
                            offset += <?= $limit ?>;
                            addClickListenersToNewRows();
                        } else {
                            offset = totalContacts;
                        }
                        loading = false;
                        document.getElementById('loading').classList.add('d-none');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loading = false;
                        document.getElementById('loading').classList.add('d-none');
                    });
            }

            function showEditContactModal() {
                // console.log('Fetching contact details for ID:', currentContactId);

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
                        // console.log('Contact details:', data);

                        document.getElementById('editFirstName').value = data.first_name;
                        document.getElementById('editLastName').value = data.last_name;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editPhone').value = data.phone;
                        document.getElementById('editAddress').value = data.address;
                        document.getElementById('editCity').value = data.city;
                        document.getElementById('editState').value = data.state;
                        document.getElementById('editZipCode').value = data.zip_code;
                        document.getElementById('editCompany').value = data.company;

                        editContactModal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load contact details: ' + error.message || 'Unknown error');
                    });
            }

            // Add click listeners to new rows
            function addClickListenersToNewRows() {
                const newRows = document.querySelectorAll('.contact-row:not(.has-listener)');
                newRows.forEach(row => {
                    row.addEventListener('click', function(e) {
                        if (!e.target.closest('.form-check')) {
                            currentContactId = this.dataset.id;
                            // console.log('Clicked contact ID:', currentContactId); // Debug log
                            if (currentContactId) {
                                showContactDetails(currentContactId);
                            } else {
                                console.error('Contact ID is undefined for row:', this); // Debug log
                            }
                        }
                    });
                    row.classList.add('has-listener');
                });
            }



            // Scroll event listener
            window.addEventListener('scroll', function() {
                if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 500)) {
                    loadMoreContacts();
                }
            });

            // Select all checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('#contactsListBody .form-check-input');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            });

            // Initial click listeners for the first 25 rows
            addClickListenersToNewRows();

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
                const notesText = document.getElementById('notesText').textContent;
                document.getElementById('editNotesTextarea').value = notesText;
                editNotesModal.show();
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

            // Delete tag click event
            document.getElementById('tagsList').addEventListener('click', function(e) {
                if (e.target.closest('.delete-tag')) {
                    const tagId = e.target.closest('.delete-tag').dataset.tagId;
                    deleteTag(currentContactId, tagId);
                }
            });

            // Save contact button click event
            document.getElementById('saveContactBtn').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('editContactForm'));
                formData.append('contact_id', currentContactId);
                updateContact(formData);
            });

            function showContactDetails(contactId) {
                // console.log('Showing details for contact ID:', contactId); // Debug log
                fetch(`get_contact_details.php?contact_id=${contactId}`)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw err;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        document.getElementById('contactName').textContent = `${data.first_name} ${data.last_name}`;
                        document.getElementById('contactEmail').textContent = data.email;
                        updateTagsDisplay(data.tags);
                        document.getElementById('notesText').textContent = data.notes || 'No notes available';

                        document.getElementById('addTagForm').dataset.contactId = data.id;

                        document.getElementById('contactsList').classList.remove('col-12');
                        document.getElementById('contactsList').classList.add('col-lg-4');
                        document.getElementById('contactDetails').classList.remove('d-none');
                        document.getElementById('contactDetails').classList.add('d-block');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(`Failed to load contact details: ${error.message || 'Unknown error'}`);
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
                        editNotesModal.hide();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(`Failed to save notes: ${error.message || 'Unknown error'}`);
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
                        alert(`Failed to add tag: ${error.message || 'Unknown error'}`);
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

            function showDeleteTagsModal() {
                console.log('Fetching tags for contact ID:', currentContactId);

                fetch(`get_delete_tags.php?contact_id=${currentContactId}`)
                    .then(response => response.json())
                    .then(tags => {
                        // console.log('Tags found:', tags);

                        let tagCount = tags.length;

                        // Clear the existing list
                        const tagsList = document.getElementById('tagsList');
                        tagsList.innerHTML = ''; // Clear all child elements

                        if (tagCount === 0) {
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

                            // Add event listeners to delete buttons
                            const deleteButtons = tagsList.querySelectorAll('.delete-tag');
                            deleteButtons.forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const tagId = this.getAttribute('data-tag-id');
                                    deleteTag(currentContactId, tagId);
                                });
                            });
                        }

                        deleteTagsModal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load tags: ' + error.message || 'Unknown error');
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
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(`Failed to delete tag: ${error.message || 'Unknown error'}`);
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
                        showContactDetails(currentContactId);
                        editContactModal.hide();
                        alert('Contact updated successfully');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(`Failed to update contact: ${error.message || 'Unknown error'}`);
                    });
            }

            addClickListenersToNewRows();

        });
    </script>
    </body>

    </html>