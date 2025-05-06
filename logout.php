<?php
session_start();  // Start the session

session_unset();
session_destroy();

// Clear the session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header('Location: ./login.php?logout=true');  
exit;
?>