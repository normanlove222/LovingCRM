<?php
require_once('init.php');

include 'header.php';
include 'menu.php';
?>


<?php //include 'sidebar.php'; 
?>
<div class="container mt-4">
    <!-- Page Content -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 'success'):
    ?>
        <div class="alert alert-success" role="alert">
            Email template saved successfully!
        </div>
    <?php endif;
    ?>

    <?php if (isset($_GET['email']) && $_GET['email'] == 'success'):
    ?>
        <div class="alert alert-success" role="alert">
            Your email broadcast was sent successfully!
        </div>
    <?php endif;
    ?>

    <div class="dashboard" style="text-align: center;">
        <h1>Welcome to Your Loving CRM Dashboard</h1>
        <?php echoCurrentDate() ?>

        <div class="stats-container" style="width: 300px; margin: 20px auto; text-align: left;">
            <h2>Stats</h2>
            <div class="stat-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">

                <div class="stat-label">Total Contacts:</div>
                <div class="stat-value"><?php echo getTotalContacts(); ?></div>
            </div>

            <div class="stat-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div class="stat-label">Last
                    <select id="dayRange" onchange="updateContactCount()">
                        <option value="30">30 days</option>
                        <option value="60">60 days</option>
                        <option value="90">90 days</option>
                        <option value="180">180 days</option>
                        <option value="365">365 days</option>
                    </select>:
                </div>
                <div class="stat-value" id="recentContacts"><?php echo getContactsInLastDays(30); ?></div>
            </div>

            <div class="stat-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div class="stat-label">Total Tags:</div>
                <div class="stat-value"><?php echo getTotalTags(); ?></div>
            </div>
        </div>
    </div>

    <div class="contacts-stats" style="width: 800px; margin: 20px auto; text-align: left; mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3>Recently Added Contacts</h3>
            <a href="contacts.php" class="btn btn-outline-primary">Manage Contacts</a>
        </div>
        <?php
        $sql = "SELECT contact_id, first_name, last_name, email, phone 
                    FROM contacts 
                    ORDER BY created_at DESC 
                    LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $contacts = $stmt->fetchAll();

        foreach ($contacts as $contact) {
            echo '<div class="contact-row" style="display: flex; justify-content: space-between; margin-bottom: 10px; gap: 70px;">
                        <div style="width: 450px;">
                          <a href="contacts.php#">'
                . $contact['first_name'] . ' ' . $contact['last_name'] .
                '</a>
                        </div>
                        <div style="width: 250px;">' . $contact['email'] . '</div>
                        <div style="width: 200px; margin-left: 50px;">' . $contact['phone'] . '</div>
                      </div>';
        }
        ?>
    </div>
    <div class="dash-emails" style="">
        <h3>Recent Email Campaigns</h3>
        <?php
        $sql = "SELECT email_id, title, created_at, response_code, sent_date, emails_count 
                    FROM emails 
                    ORDER BY created_at DESC 
                    LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $emails = $stmt->fetchAll();

        foreach ($emails as $email) {
            echo '<div class="email-row ' . ($email['response_code'] == 202 ? 'table-success' : '') . '" 
                           style="display: flex; justify-content: space-between; margin-bottom: 10px; gap: 70px;">
                        <div style="width: 550px;">' . htmlspecialchars($email['title']) . '</div>
                        <div style="width: 150px;">' . ($email['response_code'] == 202 ? 'Sent' : 'Unsent') . '</div>
                        <div style="width: 350px;">
                            <a href="campaign_builder.php?email_id=' . $email['email_id'] . '" class="btn btn-primary btn-sm">Open</a>
                            <a href="send_email.php?email_id=' . $email['email_id'] . '" class="btn btn-success btn-sm">Send</a>
                            <a href="clone_email.php?email_id=' . $email['email_id'] . '" class="btn btn-dark btn-sm">Clone</a>
                        </div>
                      </div>';
        }
        ?>
    </div>

    <style>

    </style>

    <?php
    include 'footer.php';
    include 'scripts.php';

    ?>

    <!-- local javascript co -->
    <script>
        function updateContactCount() {
            const days = document.getElementById('dayRange').value;
            fetch(`get_contacts_count.php?days=${days}`)
                .then(response => response.text())
                .then(count => {
                    document.getElementById('recentContacts').textContent = count;
                });
        }
    </script>

    </body>

    </html>