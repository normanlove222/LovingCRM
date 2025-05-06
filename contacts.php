<?php
require_once('init.php');
include 'header.php';
include 'menu.php';

// Get initial 25 most recent contacts
$query = "SELECT * FROM contacts ORDER BY created_at DESC LIMIT 25";
$contacts = getContactsFromQuery($query);
?>


<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Find & Manage Contacts</h1>
        <a href="add_contact.php" class="btn btn-primary">Add Contact</a>
    </div>
    <p><i>All searches return matches to first name, last name, and email.</i></p>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <label for="searchInput" class="me-2">Search:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Search contacts...">
            </div>
        </div>
    </div>

    <div id="contactsList">
        <?php foreach ($contacts as $contact): ?>
            <div class="contact-row d-flex flex-wrap align-items-center p-2 border-bottom"
                data-id="<?= $contact['contact_id'] ?>"
                style="cursor: pointer;">
                <div class="col-12 col-md-3">
                    <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= htmlspecialchars($contact['email']) ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= htmlspecialchars($contact['phone']) ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= htmlspecialchars($contact['state']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Contact Details Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

        <?php include 'footer.php' ?>
        <?php include 'scripts.php'; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
                const editNotesModal = new bootstrap.Modal(document.getElementById('editNotesModal'));
                const deleteTagsModal = new bootstrap.Modal(document.getElementById('deleteTagsModal'));
                const editContactModal = new bootstrap.Modal(document.getElementById('editContactModal'));
                let currentContactId;

                // Get searchquery parameter from URL
                const urlParams = new URLSearchParams(window.location.search);
                const searchQuery = urlParams.get('searchquery');

                // If searchquery exists, set it in search input and trigger search
                if (searchQuery) {
                    const searchInput = document.getElementById('searchInput');
                    searchInput.value = searchQuery;

                    // Fetch search results
                    const contactsList = document.getElementById('contactsList');
                    fetch(`rapid_search_contacts.php?term=${encodeURIComponent(searchQuery)}`)
                        .then(response => response.text())
                        .then(html => {
                            contactsList.innerHTML = html;
                            if (html.trim()) {
                                addClickListeners();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            contactsList.innerHTML = '<div class="p-3 text-danger">Error performing search.</div>';
                        });
                }

                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                }

                // Search functionality
                // Search functionality
                document.getElementById('searchInput').addEventListener('input', function(e) {
                    const searchTerm = e.target.value.trim();
                    const contactsList = document.getElementById('contactsList');

                    if (searchTerm.length > 0) {
                        fetch(`rapid_search_contacts.php?term=${encodeURIComponent(searchTerm)}`)
                            .then(response => response.text())
                            .then(html => {
                                contactsList.innerHTML = html;
                                if (html.trim()) {
                                    addClickListeners();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                contactsList.innerHTML = '<div class="p-3 text-danger">Error performing search.</div>';
                            });
                    } else {
                        contactsList.innerHTML = originalContactsList;
                        addClickListeners();
                    }
                });

                const originalContactsList = document.getElementById('contactsList').innerHTML;

                // Add click listeners to contact rows
                function addClickListeners() {
                    document.querySelectorAll('.contact-row').forEach(row => {
                        row.addEventListener('click', function() {
                            currentContactId = this.dataset.id;
                            showContactDetails(currentContactId);
                        });
                    });
                }

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

                // Save contact button click event
                document.getElementById('saveContactBtn').addEventListener('click', function() {
                    const formData = new FormData(document.getElementById('editContactForm'));
                    formData.append('contact_id', currentContactId);
                    updateContact(formData);
                });

                function showContactDetails(contactId) {
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
                            document.getElementById('contactName').textContent = `${data.first_name} ${data.last_name}`;
                            document.getElementById('contactEmail').textContent = data.email;
                            document.getElementById('contactPhone').textContent = data.phone;
                            document.getElementById('contactAddress').textContent = data.address;
                            document.getElementById('contactLocation').textContent = `${data.city}, ${data.state} ${data.zip_code}`;
                            document.getElementById('contactCompany').textContent = data.company;

                            updateTagsDisplay(data.tags);
                            document.getElementById('notesText').textContent = data.notes || 'No notes available';

                            contactModal.show();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(`Failed to load contact details: ${error.message || 'Unknown error'}`);
                        });
                }

                function showEditContactModal() {
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

                            editContactModal.show();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to load contact details: ' + error.message || 'Unknown error');
                        });
                }

                function showDeleteTagsModal() {
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

                            deleteTagsModal.show();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to load tags: ' + error.message || 'Unknown error');
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
                            showContactDetails(currentContactId);
                            editContactModal.hide();
                            alert('Contact updated successfully');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(`Failed to update contact: ${error.message || 'Unknown error'}`);
                        });
                }

                // Delete tag functionality
                document.getElementById('tagsList').addEventListener('click', function(e) {
                    if (e.target.closest('.delete-tag')) {
                        const tagId = e.target.closest('.delete-tag').dataset.tagId;
                        //delete the tag from the contact
                        deleteTag(currentContactId, tagId);
                    }
                });

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

                // Initial setup
                addClickListeners();
            });
        </script>