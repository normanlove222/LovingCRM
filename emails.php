<?php
require_once('init.php');
include 'header.php';
include 'menu.php';

// Query to get all emails from the database
$stmt = $pdo->prepare("SELECT email_id, title, created_at, updated_at, response_code, sent_date, emails_count FROM emails ORDER BY created_at DESC");
$stmt->execute();
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Email Campaigns</h1>
        <a href="new_email.php" class="btn btn-primary">Create New Campaign</a>
    </div>

    <!-- display any messages passed to this page to show success or error -->
    <?php include 'messaging.php'; ?>


    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Title</th>
                <th>Created Date</th>
                <th>Emails Sent</th>
                <th>Status</th>
                <th>Sent Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($emails) > 0): ?>
                <?php foreach ($emails as $email): ?>
                   <tr <?php echo ($email['response_code'] == 202) ? 'class="table-success"' : ''; ?>>
                    <td><?php echo htmlspecialchars(truncate_text($email['title'])); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($email['created_at'])); ?></td>
                    <td><?php echo ($email['emails_count'] !== null) ? $email['emails_count'] : ''; ?></td>
                    <td><?php echo ($email['response_code'] == 202) ? 'Sent' : 'Unsent'; ?></td>
                    <td><?php echo !empty($email['sent_date']) ? date('Y-m-d H:i', strtotime($email['sent_date'])) : 'Not sent'; ?></td>
                    <td>
                        <a href="campaign_builder.php?email_id=<?php echo $email['email_id']; ?>" class="btn btn-primary btn-sm">
                            Open
                        </a>
                        <a href="send_email.php?email_id=<?php echo $email['email_id']; ?>" class="btn btn-success btn-sm">
                            Send via Tags
                        </a>
                        <a href="send_email_using_lists.php?email_id=<?php echo $email['email_id']; ?>" class="btn btn-success btn-sm">
                            Send Via Lists
                        </a>
                            <form action="clone_email.php" method="POST" style="display:inline;">
                                <input type="hidden" name="email_id" value="<?php echo $email['email_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="btn btn-dark btn-sm">Clone</button>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No saved email templates found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include 'footer.php';
include 'scripts.php';
?>

</body>

</html>