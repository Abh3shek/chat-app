<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
$title = "Login";
include '../includes/header.php';
$message='';

if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["login"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if(empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger text-center">Please fill in all fields</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
        $stmt->execute(["email"=>$email]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($password, $user["password"])){
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: index.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger text-center">Invalid email or password</div>';
        }
    }

}
?>

<div class="d-flex justify-content-center mt-5 vh-100">
  <div class="w-50">
    <h2 class="text-center mb-4">Login</h2>

    <?= $message ?>

    <form action="login.php" method="POST" class="needs-validation" novalidate>
    <!-- Email input -->
    <div class="mb-3">
        <label for="inputEmail" class="form-label">Email address</label>
        <input type="email" class="form-control" id="inputEmail" name="email" placeholder="Enter email" required>
    </div>

    <!-- Password input with toggle -->
    <div class="mb-3 position-relative">
        <label for="inputPassword" class="form-label">Password</label>
        <input type="password" class="form-control pe-5" id="inputPassword" name="password" placeholder="Enter password" required>
        <span id="togglePassword" class="position-absolute top-50 end-0 me-3" style="cursor: pointer;">
            👁️
        </span>
    </div>

    <!-- Submit button -->
    <div class="d-grid">
        <button type="submit" name="login" class="btn btn-dark">Login</button>
    </div>
    </form>
  </div>
</div>

<?php
include '../includes/footer.php';
?>