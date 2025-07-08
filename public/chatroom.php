<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

if(!isset($_GET["room_id"]) || empty($_GET["room_id"])) {
    header("Location: index.php");
    exit();
}

$room_id = (int) $_GET['room_id'];

$stmt = $pdo->prepare("SELECT name, description FROM chat_rooms WHERE id = :id");
$stmt->execute(['id'=>$room_id]);
$room = $stmt->fetch();

if(!$room) {
    header("Location: index.php");
    exit();
}

$title = $room['name'] . " Chatroom";
include '../includes/header.php';  // This includes the navbar
?>

<div class="container mt-5">
    <div class="col-md-6 d-flex align-items-center gap-3">
        <h4><a href="index.php" class="cursor-pointer link-dark"><i class="bi bi-arrow-left"></i></a></h4>
        <div class="mb-2 vr"></div>
        <h3 style="cursor: pointer;" class="pe-3" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="<?php echo htmlspecialchars($room['description']); ?>" Tooltip on right>
            <?php echo htmlspecialchars($room['name']); ?> Chat Room
        </h3>

    </div>
    <p><?php echo htmlspecialchars($room['description']); ?></p>
    
    <p>Messages will load here in the next phase...</p>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>v
<script>
  // Manually initialize the tooltip for the heading
  var tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });
</script>

<?php
include '../includes/footer.php';  // Include the footer
?>
