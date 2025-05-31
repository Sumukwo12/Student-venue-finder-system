<?php
require_once '../config/db.php';
require_once 'includes/auth.php';

// Check if admin is logged in
checkAdminAuth();

// Get admin info
$admin = getAdminInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Venue Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-header-left">
                    <h1 class="admin-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>
                <div class="admin-header-right">
                    <div class="admin-profile">
                        <span class="admin-name"><?php echo htmlspecialchars($admin['name']); ?></span>
                        <div class="admin-avatar">
                            <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-main">