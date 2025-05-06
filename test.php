     <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__FILE__) . '/error.log');
        // Test log entry
        error_log("Test log entry: This should appear in the error log.");

        // Trigger an error
        echo $undefined_variable;
        ?>