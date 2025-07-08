<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
$title = "Register";
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: register.php?error=Please fill in all fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=Invalid email format");
        exit();
    }

    // Check if username or email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    if ($stmt->fetch()) {
        header("Location: register.php?error=Username or email already taken");
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    try {
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        header("Location: register.php?success=1");
        exit();
    } catch (PDOException $e) {
        header("Location: register.php?error=Registration failed");
        exit();
    }
}
?>

<div class="d-flex justify-content-center mt-5 vh-100">
  <div style="width: 60%;">
    <h2 class="text-center mb-4">Register</h2>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger text-center">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success text-center">
        Registration successful. <a href="login.php">Login here</a>.
      </div>
    <?php endif; ?>

    <form class="row g-3" action="register.php" method="POST">

      <div class="col-md-6">
        <label for="inputUserName" class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text">@</span>
          <input type="text" name="username" class="form-control" id="inputUserName" placeholder="john_doe" required>
        </div>
      </div>

      <div class="col-md-6">
        <label for="inputEmail4" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" id="inputEmail4" placeholder="doejohn@gmail.com" required>
      </div>

      <div class="col-md-6 position-relative">
        <label for="inputPassword4" class="form-label">Password</label>
        <input type="password" name="password" class="form-control pe-5" id="inputPassword4" placeholder="Password" required>
        <span id="togglePassword" class="position-absolute top-50 end-0 me-3" style="cursor: pointer;">
            👁️
        </span>
    </div>

      <div class="col-12">
        <button type="submit" name="register" class="btn btn-dark">Sign up</button>
      </div>
    </form>
  </div>
</div>


<?php
include '../includes/footer.php';
?>