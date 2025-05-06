<?php
require_once('init.php');
include 'header.php';
include 'menu.php';

// Get email data from database
if (isset($_GET['email_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM emails WHERE email_id = ?");
    $stmt->execute([$_GET['email_id']]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
        flex-direction: column;
    }

    .spinner-border-lg {
        width: 5rem;
        height: 5rem;
        border-width: 0.5rem;
    }
</style>

<div class="container mt-4">
    <div style="text-align: center;">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="emails.php" style="text-decoration: none; color: inherit;">
                <span style="font-size: 24px; font-weight: bold;">&#x2190; </span>Back to Emails
            </a>
        </div>
        <h1>Edit Email Campaign</h1>
        <?php echoCurrentDate(); ?>

        <div class="input-group mb-4 mt-4 col-md-4">
            <label for="title">Title: </label>
            <input type="text" class="form-control" placeholder="Email Campaign Title" id="title" name="title" disabled>
            <label for="subject">Subject: </label>
            <input type="text" class="form-control" placeholder="Enter Email Subject line" id="subject" name="subject" disabled>
            <div class="input-group-append">
                <button id="save-button" class="btn btn-primary" disabled>Save Changes</button>
            </div>
        </div>

        <div id="loading-spinner" class="spinner-container">
            <div class="spinner-border text-primary spinner-border-lg" role="status"></div>
            <div style="margin-top: 10px;">Loading Email Editor...</div>
        </div>

        <script src="https://editor.unlayer.com/embed.js"></script>
        <div id="editor-container"></div>

        <?php
        include 'footer.php';
        include 'scripts.php';
        ?>

        <script>
            // Store email data from PHP
            const emailData = <?php echo json_encode($email); ?>;
            //settings for unlayer email builder that is embeddedon page
            unlayer.init({
                id: 'editor-container',
                projectId: 254779,
                displayMode: 'email',
                onReady: function(unlayer) {
                    unlayer.setBodyValues({
                        contentWidth: '900px'
                    });
                }
            });

            // Check if the editor is ready and set up the save functionality
            unlayer.addEventListener('editor:ready', function() {
                // Load existing design if available
                if (emailData && emailData.json_content) {
                    unlayer.loadDesign(JSON.parse(emailData.json_content));
                    document.getElementById('title').value = emailData.title || '';
                    document.getElementById('subject').value = emailData.subject || '';
                }

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

                        // Send both JSON and HTML data to save_design.php
                        fetch('save_design.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    email_id: emailData.email_id,
                                    title: title,
                                    subject: subject,
                                    design: designData,
                                    html: htmlContent
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    window.location.href = data.redirect;
                                } else {
                                    alert("Error: " + data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error details:", error);
                                console.error("Error message:", error.message);
                                console.error("Error stack:", error.stack);
                                alert("There was an error processing the save. Check console for details.");
                            });
                    });
                });
            });
        </script>
    </div>
</div>
</body>

</html>