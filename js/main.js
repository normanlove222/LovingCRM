document.addEventListener('DOMContentLoaded', function() {
    let offset = <?= $limit ?>;
    let loading = false;

    //for editing notes on list_contacts
    const editNotesModal = new bootstrap.Modal(document.getElementById('editNotesModal'));
    let currentContactId;

    
    // Add Tag button click event
    document.getElementById('addTagBtn').addEventListener('click', function() {
        toggleContent('addTagContent');
    });

    // Add Tag form submit event
    document.getElementById('addTagForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const contactId = this.dataset.contactId;
        const newTag = document.getElementById('newTagInput').value;
        addTagToContact(contactId, newTag);
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#contactsListBody .form-check-input');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Row click event
    document.getElementById('contactsListBody').addEventListener('click', function(e) {
        const row = e.target.closest('.contact-row');
        if (row && !e.target.closest('.form-check')) {
            const contactId = row.dataset.id;
            showContactDetails(contactId);
        }
    });

    // Tags button click event
    document.getElementById('tagsBtn').addEventListener('click', function() {
        toggleContent('tagsContent');
    });

    // Notes button click event
    document.getElementById('notesBtn').addEventListener('click', function() {
        toggleContent('notesContent');
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


    // Lazy loading
    window.addEventListener('scroll', function() {
        if (loading) return;

        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loading = true;
            document.getElementById('loading').classList.remove('d-none');

            fetch(`get_more_contacts.php?offset=${offset}&limit=${<?= $limit ?>}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('contactsListBody').insertAdjacentHTML('beforeend', data);
                    offset += <?= $limit ?>;
                    loading = false;
                    document.getElementById('loading').classList.add('d-none');
                });
        }
    });

   function showContactDetails(contactId) {
        fetch(`get_contact_details.php?id=${contactId}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
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
                document.getElementById('notesContent').textContent = data.notes || 'No notes available';

                // Set the contact ID for the Add Tag form
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
        tagsContent.innerHTML = tags.map(tag => `<span class="badge bg-primary me-1">${tag}</span>`).join('');
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
});
