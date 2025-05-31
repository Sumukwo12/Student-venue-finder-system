<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$student = getCurrentStudent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Booking System - Laikipia University</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1>Venue Booking System</h1>
        </div>
        <div class="header-left">
            <img src="assets/images/logo.png" alt="Laikipia University Logo" class="logo">
            <div class="profile-dropdown">
                <button class="profile-btn">
                    <i class="user-icon"></i>
                    <span><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                </button>
                <div class="dropdown-content">
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <main class="container">