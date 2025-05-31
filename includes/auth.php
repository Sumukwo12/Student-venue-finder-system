<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['student_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

// Get current student info
function getCurrentStudent() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['student_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>