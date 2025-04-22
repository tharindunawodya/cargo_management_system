<?php
// Ensure no output before headers
if (ob_get_length()) ob_clean();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect to login
header("Location: ../../cargo_management_system/pages/login.php");
exit();              
?>