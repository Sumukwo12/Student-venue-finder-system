<?php

session_start(); // Ensure session starts at the top
// Admin authentication functions

function checkAdminAuth() {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

function getAdminInfo() {
    global $pdo;
    
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM administrators WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function adminLogin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM administrators WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && $password === $admin['password']) { // Direct comparison
        $_SESSION['admin_id'] = $admin['id'];
        return true;
    }
    
    return false;
}

function adminLogout() {
    unset($_SESSION['admin_id']);
    session_destroy();
    header("Location: login.php");
    exit;
}