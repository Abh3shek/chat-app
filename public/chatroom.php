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

<div class="container mt-5" style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h4 class="m-0"><a href="index.php" class="link-dark"><i class="bi bi-arrow-left"></i></a></h4>
            <h3 class="m-0" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo htmlspecialchars($room['description']); ?>">
                <?php echo htmlspecialchars($room['name']); ?> Chat Room
            </h3>
        </div>
        <p id="status" class="m-0 text-success">Connecting...</p>
    </div>
    <hr class="hr">
    <h6>Active Users:</h6>
    <ul id="userList" class="d-flex flex-wrap list-unstyled mb-2 gap-2"></ul>

    <div id="chatbox" class="border rounded bg-white p-2 mb-3" style="height: 500px; overflow-y: auto;">
        <!-- Messages will appear here -->
    </div>

    <div class="input-group mb-3">
        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." autofocus>
        <button class="btn btn-success" id="sendButton">Send</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatbox = document.getElementById('chatbox');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const status = document.getElementById('status');
    const conn = new WebSocket('ws://localhost:8080/chat');

    // Load previous messages with structured UI
    <?php foreach ($messages as $message): ?>
    (function(){
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('d-flex', 'mb-1');

        const msgBubble = document.createElement('div');
        msgBubble.classList.add('p-2', 'rounded', 'shadow-sm', 'd-flex', 'flex-column', 'mb-2');
        // msgBubble.style.maxWidth = '70%';
        msgBubble.style.wordWrap = 'break-word';

        <?php
            $time = date('H:i', strtotime($message['timestamp']));
            $username = htmlspecialchars($message['username']);
            $text = htmlspecialchars($message['message_text']);
            $isCurrentUser = $message['username'] === $_SESSION['username'];
        ?>

        if (<?php echo $isCurrentUser ? 'true' : 'false'; ?>) {
            msgDiv.classList.add('justify-content-end');
            msgBubble.classList.add('bg-secondary-subtle', 'text-dark');
        } else {
            msgDiv.classList.add('justify-content-start');
            msgBubble.classList.add('bg-dark', 'text-white');
        }

        const userDiv = document.createElement('div');
        userDiv.classList.add('text-start', 'small', 'fw-semibold');
        userDiv.textContent = <?php echo json_encode($username); ?>;

        const messageP = document.createElement('p');
        messageP.classList.add('m-1', 'text-center');
        messageP.textContent = <?php echo json_encode($text); ?>;

        const timeDiv = document.createElement('div');
        timeDiv.classList.add('text-end', 'small');
        timeDiv.textContent = <?php echo json_encode($time); ?>;

        msgBubble.appendChild(userDiv);
        msgBubble.appendChild(messageP);
        msgBubble.appendChild(timeDiv);

        msgDiv.appendChild(msgBubble);
        chatbox.appendChild(msgDiv);
    })();
    <?php endforeach; ?>

    chatbox.scrollTop = chatbox.scrollHeight;

    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

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
        try {
            const data = JSON.parse(e.data);

            if (data.type === "user_list") {
                const userList = document.getElementById('userList');
                userList.innerHTML = '';
                data.users.forEach(user => {
                    const li = document.createElement('li');
                    li.classList.add("badge", "bg-secondary", "p-2");
                    li.textContent = `@${user}`;
                    userList.appendChild(li);
                });
            } else if (data.type === "chat_message") {
                const msgDiv = document.createElement('div');
                msgDiv.classList.add('d-flex', 'mb-1');

                const msgBubble = document.createElement('div');
                msgBubble.classList.add('p-2', 'rounded', 'shadow-sm', 'd-flex', 'flex-column');
                // msgBubble.style.maxWidth = '70%';
                msgBubble.style.wordWrap = 'break-word';

                if (data.username === <?php echo json_encode($_SESSION['username']); ?>) {
                    msgDiv.classList.add('justify-content-end');
                    msgBubble.classList.add('bg-secondary-subtle', 'text-dark');
                } else {
                    msgDiv.classList.add('justify-content-start');
                    msgBubble.classList.add('bg-light');
                }

                const userDiv = document.createElement('div');
                userDiv.classList.add('text-end', 'small', 'fw-semibold');
                userDiv.textContent = data.username;

                const messageP = document.createElement('p');
                messageP.classList.add('m-1', 'text-center');
                messageP.textContent = data.message;

                const timeDiv = document.createElement('div');
                timeDiv.classList.add('text-end', 'small');
                timeDiv.textContent = data.time;

                msgBubble.appendChild(userDiv);
                msgBubble.appendChild(messageP);
                msgBubble.appendChild(timeDiv);

                msgDiv.appendChild(msgBubble);
                chatbox.appendChild(msgDiv);
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        } catch {
            const msgDiv = document.createElement('div');
            msgDiv.classList.add('d-flex', 'mb-1', 'justify-content-start');

            const msgBubble = document.createElement('div');
            msgBubble.classList.add('p-2', 'rounded', 'shadow-sm', 'bg-light', 'd-flex', 'flex-column');
            // msgBubble.style.maxWidth = '70%';
            msgBubble.style.wordWrap = 'break-word';

            const messageP = document.createElement('p');
            messageP.classList.add('m-1', 'text-center');
            messageP.textContent = e.data;

            msgBubble.appendChild(messageP);
            msgDiv.appendChild(msgBubble);
            chatbox.appendChild(msgDiv);
            chatbox.scrollTop = chatbox.scrollHeight;
        }
    };

    conn.onclose = () => {
        status.textContent = "Disconnected from chat server.";
        status.classList.replace('text-success', 'text-danger');
    };

    conn.onerror = (error) => {
        console.error("WebSocket error:", error);
        status.textContent = "Error connecting to chat server.";
        status.classList.replace('text-success', 'text-danger');
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
});
</script>

<?php include '../includes/footer.php'; ?>
