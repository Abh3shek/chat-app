<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=realtime_chat_app;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userRooms; // Map connection IDs to room IDs
    protected $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->userRooms = [];
        $this->pdo = $pdo;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) {
            echo "Invalid message format received.\n";
            return;
        }

        if ($data['type'] === 'join') {
            if (!isset($data['room_id'], $data['username'])) {
                echo "Invalid join message format received.\n";
                return;
            }
            $room_id = $data['room_id'];
            $username = htmlspecialchars($data['username']);
            $this->userRooms[$from->resourceId] = $room_id;
            echo "User {$username} joined room {$room_id} (Conn: {$from->resourceId})\n";
            return; // Do not broadcast join messages
        }

        // Normal chat message validation
        if (!isset($data['room_id'], $data['username'], $data['message'])) {
            echo "Invalid chat message format received.\n";
            return;
        }

        $room_id = $data['room_id'];
        $username = htmlspecialchars($data['username']);
        $message = htmlspecialchars($data['message']);

        $formattedMessage = "{$username}: {$message}";
        echo "Broadcasted in room {$room_id}: {$formattedMessage}\n";

        // Insert the message into the database
        $stmt = $this->pdo->prepare("INSERT INTO messages (room_id, user_id, message_text) VALUES (:room_id, :user_id, :message_text)");
        $stmt->execute([
            'room_id' => $room_id,
            'user_id' => $data['user_id'], // We'll handle this below
            'message_text' => $message
        ]);


        // Broadcast to all clients in the same room
        foreach ($this->clients as $client) {
            $clientId = $client->resourceId;
            if (isset($this->userRooms[$clientId]) && $this->userRooms[$clientId] == $room_id) {
                $client->send($formattedMessage);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->userRooms[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$app = new App('localhost', 8080);
$app->route('/chat', new Chat($pdo), ['*']);

$app->run();
