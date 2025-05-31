<div class="admin-sidebar">
    <div class="sidebar-header">
        <img src="../assets/images/logo.png" alt="Laikipia University Logo" class="sidebar-logo">
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="sidebar-icon">📊</i>
            <span>Dashboard</span>
        </a>
        <a href="bookings.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
            <i class="sidebar-icon">📝</i>
            <span>Bookings</span>
        </a>
        <a href="upload.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload.php' ? 'active' : ''; ?>">
            <i class="sidebar-icon">📤</i>
            <span>Upload</span>
        </a>
        <a href="feedback.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
            <i class="sidebar-icon">💬</i>
            <span>Feedback</span>
        </a>
        <a href="venues.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'venues.php' ? 'active' : ''; ?>">
            <i class="sidebar-icon">🏫</i>
            <span>Manage Venues</span>
        </a>
        <a href="logout.php" class="sidebar-link">
            <i class="sidebar-icon">🚪</i>
            <span>Logout</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <p>Admin Panel</p>
        <small>Venue Booking System</small>
    </div>
</div>