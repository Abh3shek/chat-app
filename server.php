<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=realtime_chat_app;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userRooms;      // resourceId => room_id
    protected $connectionUsers; // resourceId => username
    protected $roomUsers;      // room_id => [usernames]
    protected $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->userRooms = [];
        $this->connectionUsers = [];
        $this->roomUsers = [];
        $this->pdo = $pdo;
    }

    protected function broadcastUserList($room_id) {
        $userList = [
            'type' => 'user_list',
            'users' => array_values($this->roomUsers[$room_id] ?? [])
        ];
        $json = json_encode($userList);

        foreach ($this->clients as $client) {
            $clientId = $client->resourceId;
            if (isset($this->userRooms[$clientId]) && $this->userRooms[$clientId] == $room_id) {
                $client->send($json);
            }
        }
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
            $this->connectionUsers[$from->resourceId] = $username;

            if (!isset($this->roomUsers[$room_id])) {
                $this->roomUsers[$room_id] = [];
            }
            if (!in_array($username, $this->roomUsers[$room_id])) {
                $this->roomUsers[$room_id][] = $username;
            }

            echo "User {$username} joined room {$room_id} (Conn: {$from->resourceId})\n";
            $this->broadcastUserList($room_id);
            return; // Do not broadcast join messages
        }

        // Validate normal chat message
        if (!isset($data['room_id'], $data['username'], $data['message'])) {
            echo "Invalid chat message format received.\n";
            return;
        }

        $room_id = $data['room_id'];
        $username = htmlspecialchars($data['username']);
        $message = htmlspecialchars($data['message']);

        // Insert message into the database
        $stmt = $this->pdo->prepare("INSERT INTO messages (room_id, user_id, message_text) VALUES (:room_id, :user_id, :message_text)");
        $stmt->execute([
            'room_id' => $room_id,
            'user_id' => $data['user_id'],
            'message_text' => $message
        ]);

        $lastId = $this->pdo->lastInsertId();
        $stmt = $this->pdo->prepare("SELECT timestamp FROM messages WHERE id = :id");
        $stmt->execute(['id' => $lastId]);
        $row = $stmt->fetch();
        $time = date('H:i', strtotime($row['timestamp']));
        
        $formattedMessage = json_encode([
            'type' => 'chat_message',
            'username' => $username,
            'message' => $message,
            'time' => $time
        ]);

        echo "Broadcasted in room {$room_id}: {$username} [{$time}]: {$message}\n";

        // Broadcast message to all clients in the room
        foreach ($this->clients as $client) {
            $clientId = $client->resourceId;
            if (isset($this->userRooms[$clientId]) && $this->userRooms[$clientId] == $room_id) {
                $client->send($formattedMessage);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $resourceId = $conn->resourceId;
        $room_id = $this->userRooms[$resourceId] ?? null;
        $username = $this->connectionUsers[$resourceId] ?? null;

        if ($room_id !== null && $username !== null) {
            if (($key = array_search($username, $this->roomUsers[$room_id])) !== false) {
                unset($this->roomUsers[$room_id][$key]);
                $this->broadcastUserList($room_id);
            }
        }

        $this->clients->detach($conn);
        unset($this->userRooms[$resourceId]);
        unset($this->connectionUsers[$resourceId]);

        echo "Connection {$resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$app = new App('localhost', 8080);
$app->route('/chat', new Chat($pdo), ['*']);
$app->run();
