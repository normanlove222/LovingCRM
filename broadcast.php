<?php
require_once('init.php');

include 'header.php';
include 'menu.php';

// At the top of campaign_builder.php where you fetch the data
$designData = null;
$emailTitle = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT json_content, title FROM emails WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($email) {
        $designData = $email['json_content'];
        $emailTitle = $email['title'];
    }
}
?>

<style>
    #editor-container {
        height: 800px;
        width: 100%;
        margin: 20px 0;
    }
</style>
<div class="container mt-4">
    <div style="text-align: center;">
        <h1>Send a Broadcast</h1>
        <?php echoCurrentDate(); ?>
        <!-- <button id="save-button" class="btn btn-primary">Save Design</button> -->

        <div class="input-group mb-4 mt-4 col-md-4">
            <input type="text" class="form-control" placeholder="email" id="title" name="title"
                value="<?php echo htmlspecialchars($emailTitle ?? ''); ?>">
            <div class="input-group-append">
                <button id="save-button" class="btn btn-primary">Save Design</button>
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

            // Ensure editor is ready before setting up save functionality
            unlayer.addEventListener('editor:ready', function() {

                // Load the design if JSON data is available
                <?php if ($designData): ?>
                    unlayer.loadDesign(<?php echo $designData; ?>);
                <?php endif; ?>

                // Handle Save button click
                document.getElementById('save-button').addEventListener('click', function() {

                    // Get the title value
                    const title = document.getElementById('title').value.trim();

                    // Check if title is empty
                    if (!title) {
                        alert('Please enter a title for your email');
                        return;
                    }

                    // Export design JSON and HTML data
                    unlayer.exportHtml(function(data) {
                        const designData = data.design; // JSON design data
                        const htmlContent = data.html; // HTML content

                        // Prepare save URL with ID for updating
                        const urlParams = new URLSearchParams(window.location.search);
                        const id = urlParams.get('id');
                        let saveUrl = 'save_edit_design.php?id=' + id;

                        // Send JSON and HTML data to save_edit_design.php
                        fetch(saveUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    title: title, // Add title 
                                    design: designData,
                                    html: htmlContent
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
<?php include 'footer.php'; ?>
<?php include 'scripts.php'; ?>
</body>

</html>