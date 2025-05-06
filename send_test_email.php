<?php
require_once 'init.php';
require 'vendor/autoload.php';

// Demo mode check
if ((isset($environment) && $environment === 'demo') || (defined('ENVIRONMENT') && ENVIRONMENT === 'demo')) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Demo Mode</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head><body><div class="container mt-5"><div class="alert alert-warning text-center">This feature is <b>NOT</b> available in DEMO mode.</div></div></body></html>';
    exit;
}

// Get email_id from GET
$email_id = isset($_GET['email_id']) ? intval($_GET['email_id']) : 0;

// If form submitted, send test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $test_email = trim($_POST['test_email']);
    if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Fetch email details
        $stmt = $pdo->prepare('SELECT * FROM emails WHERE email_id = ?');
        $stmt->execute([$email_id]);
        $email = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$email) {
            $error = 'Email template not found.';
        } else {
            // Prepare SendGrid email
            $sendgrid = new \SendGrid(SG_API_KEY);
            $sg_email = new \SendGrid\Mail\Mail();
            $sg_email->setFrom(SEND_EMAIL, EMAIL_NAME);
            $sg_email->setSubject('[TEST] ' . $email['subject']);
            $sg_email->addTo($test_email);
            $sg_email->addContent('text/html', $email['html_content']);

            try {
                $response = $sendgrid->send($sg_email);
                if ($response->statusCode() === 202) {
                    $success = 'Test email sent successfully to ' . htmlspecialchars($test_email) . '!';
                } else {
                    $error = 'Failed to send email. Status: ' . $response->statusCode();
                }
            } catch (Exception $e) {
                $error = 'Exception: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Test Email</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Send Test Email</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="test_email" class="form-label">Test Email Address</label>
                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="you@example.com" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Test Email</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="emails.php" class="btn btn-link">&larr; Back to Emails</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>