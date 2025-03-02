

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the register page
header('Location: register.php');
exit();
