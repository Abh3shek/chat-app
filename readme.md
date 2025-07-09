# Real-Time Chat Application

A **real-time, multi-user chat application** built for the **Web Developer Internship Assignment** to demonstrate proficiency in core web development technologies, problem-solving, and building robust, user-friendly, secure applications.

---

## 🚀 Features

✅ **User Authentication & Authorization**

- Secure user registration and login with `password_hash` + `password_verify`.
- Session management using `$_SESSION` to maintain logged-in state.
- Users must be authenticated to access chat functionalities.

✅ **Real-Time Messaging**

- Implemented using **Ratchet PHP WebSockets** for instant, real-time messaging without page refresh.
- Messages broadcast instantly to all users in the room.

✅ **Chat Rooms**

- Users can **join/leave pre-created public chat rooms** (created manually in the database).
- Previous messages in the room load automatically upon joining.

✅ **Active User List**

- Displays a **dynamically updated list of active users** in the current room.

✅ **Responsive UI**

- Clean, intuitive, and mobile-friendly chat interface for all devices.

✅ **Message Timestamps**

- Displays the time each message was sent.

✅ **XSS Prevention**

- User inputs are sanitized to prevent XSS vulnerabilities in messages.

❌ Typing Indicator: Not implemented.
❌ Desktop Notifications: Not implemented.

---

## 🛠️ Tech Stack

- HTML, CSS, JavaScript
- PHP (backend, authentication, WebSocket server)
- MySQL (database)
- Ratchet PHP (WebSocket real-time messaging)

---

---

## ⚡ Setup Instructions

1️⃣ **Clone the Repository:**

```bash
git clone <repo-link>
cd <repo-folder>
```

2️⃣ **Import Database:**

- Create a MySQL database (e.g., `realtime_chat_app`).
- Import the provided `realtime_chat_app.sql` file:

3️⃣ **Configure Database Connection:**

- Update `config.php` with your database credentials:

```php
$host = 'localhost';
$db   = 'realtime_chat_app';
$user = 'root';
$pass = '';
```

4️⃣ **Run WebSocket Server:**

```bash
php server.php
```

Keep this terminal running to maintain WebSocket real-time functionality.

5️⃣ **Start the Application:**

- Serve your PHP project using Apache, XAMPP, Laragon, or `php -S localhost:8000`.
- Open `http://localhost/` in your browser.

---

## 🪪 Sample Login Credentials

| Username | Email                                       | Password |
| -------- | ------------------------------------------- | -------- |
| testuser | [test@example.com](mailto:test@example.com) | 123456   |

You may register new users or use the above sample (if pre-inserted) to test the application.

---

## 📜 Database Structure

**users**:

- `id`, `username`, `email`, `password`, `created_at`

**chat_rooms**:

- `id`, `name`, `description`, `created_at`

**messages**:

- `id`, `room_id`, `user_id`, `message_text`, `timestamp`

---

## ✅ Completion Status

- All **core requirements completed**.
- Optional features (typing indicator and notifications) not implemented.

---

## 📧 Contact

For questions or clarifications, please contact:
**[Abh3shek](https://github.com/Abh3shek)**

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
