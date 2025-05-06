<?php
require_once('init.php');
include 'header.php';
include 'menu.php';

// Query to get THE EMAIL VIA THE ID
if (isset($_GET['email_id'])) {
    $id = $_GET['email_id'];
    $stmt = $pdo->prepare("SELECT email_id, title, subject FROM emails WHERE email_id = ? limit 1");
    $stmt->execute([$id]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch all tags
    $tagsQuery = "SELECT DISTINCT name FROM tags";
    $stmt = $pdo->prepare($tagsQuery);
    $stmt->execute();
    $tags = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tags[] = $row['name'];
    }

    // Fetch all lists
    $listsQuery = "SELECT list_id, list_name FROM lists";
    $stmt = $pdo->prepare($listsQuery);
    $stmt->execute();
    $lists = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lists[] = $row;
    }
}
?>

<div class="container mt-4">
    <!-- Buttons at the Bottom -->
    <div style="text-align: left; margin-bottom: 20px;">
        <a href="emails.php" style="text-decoration: none; color: inherit;">
            <span style="font-size: 24px; font-weight: bold;">&#x2190; </span>Back to Emails
        </a>
    </div>
    <div class="d-flex justify-content-end gap-3">
        <a href="send_test_email.php?email_id=<?php echo $email['email_id']; ?>" class="btn btn-secondary">Send a Test</a>
        <button type="button" onclick="sendEmails()" class="btn btn-primary">Send Now</button>
    </div>
    <h2>Email Presend Details</h2>

    <div class="card my-4">
        <div class="card-body">

            <!-- Sender Section -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div>
                    <h5 class="mb-1">Sender Info</h5>
                    <p class="mb-1"><strong>Name:</strong> <?php echo EMAIL_NAME; ?></p>
                    <p class="mb-0"><strong>Email:</strong> <?php echo SEND_EMAIL; ?></p>
                </div>
                <a href="#" class="btn btn-outline-primary">Edit</a>
            </div>

            <!-- Subject Section -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div>
                    <h5 class="mb-1">Subject</h5>
                    <p class="mb-1"><strong>Subject:</strong> <?php echo $email['subject']; ?></p>
                </div>
                <a href="campaign_builder.php?email_id= <?php echo $email['email_id']; ?>" class="btn btn-outline-primary">Edit</a>
            </div>

            <!-- Audience Section -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div>
                    <h5 class="mb-1">Audience</h5>
                    <label for="list">Select a List</label>
                    <select name="lists[]" id="lists" class="select" style="width: 100%;">
                        <?php foreach ($lists as $list): ?>
                            <option value="<?php echo htmlspecialchars($list['list_id']); ?>"><?php echo htmlspecialchars($list['list_name']); ?></option>
                        <?php endforeach; ?>
                    </select><br><br>
                    <div id="contactCount">0 contacts selected.</div><br>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <div>
                    <h5 class="mb-1">Schedule</h5>
                    <p class="mb-0">Send time: <span class="text-muted">Your broadcast will be sent right away</span></p>
                </div>
                <div>
                    <select class="form-select" aria-label="Schedule Send Time">
                        <option selected>Send right away</option>
                        <option value="1">Schedule for later</option>
                    </select>
                    <a href="#" class="btn btn-outline-primary mt-2">Edit</a>
                </div>
            </div>

            <!-- Content Section -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Content</h5>
                </div>
                <a href="#" class="btn btn-outline-primary">Edit</a>
            </div>

        </div>
    </div>
</div>

<?php
include 'footer.php';
include 'scripts.php';
?>

<script>
    function sendEmails() {
        const listId = $('#lists').val();
        const id = <?php echo $id; ?>;

        window.location.href = 'process_send_multiple_emails_using_list.php?' + new URLSearchParams({
            listId: listId,
            email_id: id
        });
    }

    $(document).ready(function() {
        $('#tags').select2({
            placeholder: "Select tags",
            allowClear: true
        });

        // Fetch contact count when tags are selected
        //select audience to email via lists
        $('#lists').on('change', function() {
            var listId = $(this).val();
            if (listId) {
                $.ajax({
                    url: 'get_contact_count_selected_list.php',
                    type: 'POST',
                    data: {
                        listId: listId
                    },
                    dataType: 'json',
                    success: function(data) {
                        // console.log('this is data count: ' + data.count);
                        $('#contactCount').text(data.count + ' contacts selected.');
                    },
                    error: function() {
                        $('#contactCount').text('Error fetching contact count.');
                    }
                });
            } else {
                $('#contactCount').text('0 contacts selected.');
            }
        });
    });
</script>


</body>

</html>