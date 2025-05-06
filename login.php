<?php
// Disable caching
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
session_start();
require_once('c1.php');
require_once 'functions.php'; // Assuming you have sanitization and redirect functions

// Check if the request method is POST (i.e., form submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if both email and password are set in $_POST
    if (isset($_POST['email'], $_POST['password'])) {
        // Sanitize the inputs
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password']; // Sanitize if necessary

        // Prepare and execute the query
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify user and password
        if ($user && $password === $user['password']) {  // Assuming passwords are in plain text
            // Set session and redirect
            $_SESSION['user_id'] = $user['id'];
            redirect('index.php');  // Use a redirect function
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please enter both email and password";
    }
}

include "header.php";
include "logout_menu.php"; 
?>

<h1 style="text-align: center; margin-top: 2rem; color: #f169b3;">Welcome to Loving CRM</h1>
<img src='images/login.jpg' class="loginImage" style="display: block; margin-left: auto; margin-right: auto;">

<div class="login-container">
    <h2 style="text-align: center; color: #f169b3;">Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="login.php" method="post" style="padding: 20px;">
        <input type="email" name="email" placeholder="Email" required style="width: 95%;">
        <input type="password" name="password" placeholder="Password" required style="width: 95%;">
        <button type="submit">Login</button>
    </form>
    <div class="alert alert-info mt-3" role="alert">
        <strong>Demo Login Info:</strong><br>
        User: demo@lovingCRM.com<br>
        Password: demouser
    </div>
</div>
</body>

</html>