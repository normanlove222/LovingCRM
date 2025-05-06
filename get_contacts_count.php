<?php
require_once('init.php');

if (isset($_GET['days'])) {
    $days = (int)$_GET['days'];
    echo getContactsInLastDays($days);
} else {
    echo "0";
}
