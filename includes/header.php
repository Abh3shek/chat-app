<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : "My Site" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logo on the left -->
            <a class="navbar-brand d-flex align-items-center" href="./index.php">
            Chat App
            </a>

            <!-- Conditional buttons on the right -->
            <div class="d-flex align-items-center">
            <?php if (isset($_SESSION['username'])): ?>
                <!-- If user is logged in, show Logout button -->
                <span class="me-3">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="btn btn-dark">Logout</a>
            <?php else: ?>
                <!-- If user is not logged in, show Sign Up and Sign In buttons -->
                <a href="register.php" class="btn btn-dark me-2">Sign Up</a>
                <a href="login.php" class="btn btn-dark">Sign In</a>
            <?php endif; ?>
            </div>
        </div>
    </nav>


</nav>
