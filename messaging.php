<!-- alerts that show at top if things are successful on the email.php page, but maybe other pages as well -->
<?php if (isset($_GET['success']) && $_GET['success'] == 'success'): ?>
    <div class="alert alert-success" role="alert">
        Email template saved successfully!
    </div>
<?php endif; ?>
<?php if (isset($_GET['email']) && $_GET['email'] == 'success'): ?>
    <div class="alert alert-success" role="alert">
        Email Campaign sent successfully!!
    </div>
<?php endif; ?>
<?php if (isset($_GET['import']) && $_GET['import'] == 'success'): ?>
    <div class="alert alert-success" role="alert">
        Contacts successfully imported!
    </div>
<?php endif; ?>
<?php if (isset($_GET['export']) && $_GET['export'] == 'success'): ?>
    <div class="alert alert-success" role="alert">
        Export File downloaded successfully!
    </div>
<?php endif; ?>
<?php if (isset($_GET['clone']) && $_GET['clone'] == 'success'): ?>
    <div class="alert alert-success" role="alert">
        Email Campaign has been CLONED successfully!!
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_request_method'): ?>
    <div class="alert alert-danger" role="alert">
        Invalid Request Method!!
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_csrf'): ?>
    <div class="alert alert-danger" role="alert">
        Invalid CSRF!!
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'rate_limit'): ?>
    <div class="alert alert-danger" role="alert">
        Error: Rate Limit Exceeded!!
    </div>
<?php endif; ?>


<!-- failed messages below -->
<?php if (isset($_GET['clone']) && $_GET['clone'] == 'clone_failed'): ?>
    <div class="alert alert-danger" role="alert">
        Email Cloning has FAILED! There ws an error.
    </div>
<?php endif; ?>