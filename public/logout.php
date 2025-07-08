<?php
require_once __DIR__ . '/../includes/session.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
