<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();
include '../includes/header.php';  // This includes the navbar

session_start();  // Start session to check if the user is logged in
?>

<!-- Main content of the page -->
<div class="container mt-4">
  <?php if (isset($_SESSION['username'])): ?>
    <!-- If the user is logged in -->
    <h2>Welcome to the Chat App!</h2>
    <p>We're glad to have you here, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    <p>Database connection successful!</p>
  <?php else: ?>
    <!-- If the user is not logged in -->
    <p>Please <a href="login.php" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">sign ip</a>
    or
    <a href="register.php" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">sign up</a> to access the chat.</p>
  <?php endif; ?>
</div>

<?php
include '../includes/footer.php';  // Include the footer
?>
