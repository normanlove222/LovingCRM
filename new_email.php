<?php
session_start();  
require_once('c1.php');  

require_once 'functions.php';
require_once('require_login.php');
include 'header.php';
include 'menu.php';
?>

<style>
    #editor-container {
        height: 800px;
        width: 100%;
        margin: 20px 0;
        display: none;
    }

    .spinner-container {
        height: 800px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container mt-4">
    <div style="text-align: center;">
        <h1>Create a New Broadcast</h1>
        <?php echoCurrentDate(); ?>

        <div class="input-group mb-4 mt-4 col-md-4">
            <label for="title">Title: </label>
            <input type="text" class="form-control" placeholder="Email Campaign Title" id="title" name="title" disabled>
            <label for="subject">Subject: </label>
            <input type="text" class="form-control" placeholder="Enter Email Subject line" id="subject" name="subject" disabled>
            <div class="input-group-append">
                <button id="save-button" class="btn btn-primary" disabled>Save Design</button>
            </div>
        </div>

        <div id="loading-spinner" class="spinner-container">
            Loading Email Builder
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <script src="https://editor.unlayer.com/embed.js"></script>
        <div id="editor-container"></div>

        <script>
            // Initialize Unlayer editor
            unlayer.init({
                id: 'editor-container',
                projectId: 254779,
                displayMode: 'email'
            });

            // Check if the editor is ready and set up the save functionality
            unlayer.addEventListener('editor:ready', function() {
                // Hide spinner, show editor, enable inputs
                document.getElementById('loading-spinner').style.display = 'none';
                document.getElementById('editor-container').style.display = 'block';
                document.getElementById('title').disabled = false;
                document.getElementById('subject').disabled = false;
                document.getElementById('save-button').disabled = false;

                document.getElementById('save-button').addEventListener('click', function() {
                    // Get the title value
                    const title = document.getElementById('title').value.trim();
                    const subject = document.getElementById('subject').value.trim();

                    // Check if title is empty
                    if (!title) {
                        alert('Please enter a title for your email');
                        return;
                    }
                    if (!subject) {
                        alert('Please enter a subject for your email');
                        return;
                    }

                    // Export JSON and HTML using exportHtml method
                    unlayer.exportHtml(function(data) {
                        const designData = data.design; // JSON design data
                        const htmlContent = data.html; // HTML content

                        // Set the save URL without an ID, as this is a new design
                        let saveUrl = 'save_design.php';

                        // Send both JSON and HTML data to save_design.php
                        fetch(saveUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    title: title, // Add title 
                                    subject: subject, // Add subject 
                                    design: designData, // JSON design data
                                    html: htmlContent // HTML content
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = data.redirect;
                                } else {
                                    alert("Error: " + data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error with fetch:", error);
                                alert("There was an error processing the save. Check console for details.");
                            });
                    });
                });
            });
        </script>
    </div>
</div>

<?php
include 'footer.php';
include 'scripts.php';
?>
</body>

</html>