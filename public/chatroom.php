<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

if (!isset($_GET["room_id"]) || empty($_GET["room_id"])) {
    header("Location: index.php");
    exit();
}

$room_id = (int) $_GET['room_id'] ?? 0;

// Fetch last 50 messages
$stmt = $pdo->prepare("
    SELECT messages.*, users.username 
    FROM messages 
    JOIN users ON messages.user_id = users.id 
    WHERE messages.room_id = :room_id 
    ORDER BY messages.timestamp ASC 
    LIMIT 50
");
$stmt->execute(['room_id' => $room_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch room info
$stmt = $pdo->prepare("SELECT name, description FROM chat_rooms WHERE id = :id");
$stmt->execute(['id' => $room_id]);
$room = $stmt->fetch();

if (!$room) {
    header("Location: index.php");
    exit();
}

$title = htmlspecialchars($room['name']) . " Chatroom";
include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="col-md-6 d-flex align-items-center gap-3">
        <h4><a href="index.php" class="cursor-pointer link-dark"><i class="bi bi-arrow-left"></i></a></h4>
        <div class="mb-2 vr"></div>
        <h3 style="cursor: pointer;" class="pe-3" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="<?php echo htmlspecialchars($room['description']); ?>">
            <?php echo htmlspecialchars($room['name']); ?> Chat Room
        </h3>
    </div>
    <p><?php echo htmlspecialchars($room['description']); ?></p>
    
    <div id="chatbox" style="border: 1px solid #ccc; padding: 10px; height: 300px; overflow-y: scroll;">
        <!-- Messages will appear here -->
    </div>

    <script>
        const chatbox = document.getElementById('chatbox');
        let p;
        <?php foreach ($messages as $message): ?>
            p = document.createElement('p');
            p.textContent = <?php echo json_encode($message['username'] . ': ' . $message['message_text']); ?>;
            chatbox.appendChild(p);
        <?php endforeach; ?>
        chatbox.scrollTop = chatbox.scrollHeight;
    </script>

    <br>

    <input type="text" id="messageInput" placeholder="Type your message..." style="width: 80%;" autofocus>
    <button id="sendButton">Send</button>

    <p id="status" style="color: green;">Connecting...</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize tooltip
    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const status = document.getElementById('status');

    const conn = new WebSocket('ws://localhost:8080/chat');

    conn.onopen = () => {
        status.textContent = "Connected to chat server.";
        const joinData = {
            type: "join",
            room_id: <?php echo json_encode($room_id); ?>,
            username: <?php echo json_encode($_SESSION['username']); ?>
        };
        conn.send(JSON.stringify(joinData));
    };

    conn.onmessage = (e) => {
        console.log("Received:", e.data);
        const p = document.createElement('p');
        p.textContent = e.data;
        chatbox.appendChild(p);
        chatbox.scrollTop = chatbox.scrollHeight;
    };

    conn.onclose = () => {
        status.textContent = "Disconnected from chat server.";
        status.style.color = "red";
    };

    conn.onerror = (error) => {
        console.error("WebSocket error:", error);
        status.textContent = "Error connecting to chat server.";
        status.style.color = "red";
    };

    sendButton.addEventListener('click', () => {
        const message = messageInput.value.trim();
        if (message !== '') {
            const data = {
                type: "message",
                room_id: <?php echo json_encode($room_id); ?>,
                user_id: <?php echo json_encode($_SESSION['user_id']); ?>,
                username: <?php echo json_encode($_SESSION['username']); ?>,
                message: message
            };
            conn.send(JSON.stringify(data));
            messageInput.value = '';
            messageInput.focus();
        }
    });

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendButton.click();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
