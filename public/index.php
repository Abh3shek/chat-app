<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();
$title = "Home";
include '../includes/header.php';  // This includes the navbar

session_start();  // Start session to check if the user is logged in

// to fetch chat_rooms 
$stmt = $pdo->query("SELECT id, name, description FROM chat_rooms ORDER BY id ASC");
$chatRooms = $stmt->fetchAll();
?>

<!-- Main content of the page -->
<div class="container mt-4">
    <?php if (isset($_SESSION['username'])): ?>
        <!-- If the user is logged in -->
        <h2>Welcome to the Chat App!</h2>
        <p><?php echo htmlspecialchars($_SESSION['username']); ?>, explore the communities!</p>
    <?php else: ?>
        <!-- If the user is not logged in -->
        <p>Please <a href="login.php" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">sign in</a>
        or
        <a href="register.php" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">sign up</a> to access the chat.</p>
    <?php endif; ?>

    <h2>Available Chat Rooms</h2>

    <!-- Display chat rooms in cards -->
    <div class="row">
        <?php foreach ($chatRooms as $room): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($room['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($room['description']); ?></p>
                        <form action="chatroom.php" method="get" style="display: inline;">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                            <button class="btn btn-outline-dark" type="submit">Join</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
include '../includes/footer.php';  // Include the footer
?>
