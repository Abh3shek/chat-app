<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
include '../includes/header.php';

session_start();
if (isset($_SESSION['username'])) {
    // User is logged in
    echo "Hello, " . htmlspecialchars($_SESSION['username']) . "!";
    echo "Welcome to the Chat App!";
} else {
    // If the user is not logged in, show a message or redirect
    echo "Please log in to access the chat.";
}

echo "Database connection successful!";
?>

<?php
include '../includes/footer.php';
?>