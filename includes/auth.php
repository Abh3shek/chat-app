<?php
require_once __DIR__ . '/session.php';

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
