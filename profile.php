<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Check if user is logged in
requireLogin();

// Get current student data
$student = getCurrentStudent();

if (!$student) {
    header("Location: logout.php");
    exit;
}

$successMessage = '';
$errorMessage = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_profile') {
        // Get form data
        $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        // Validate inputs
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $errorMessage = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address';
        } else {
            // Check if email is already in use by another student
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $student['id']]);
            
            if ($stmt->rowCount() > 0) {
                $errorMessage = 'Email address is already in use';
            } else {
                // Update student profile
                try {
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$firstName, $lastName, $email, $student['id']]);
                    
                    $successMessage = 'Profile updated successfully';
                    
                    // Refresh student data
                    $student = getCurrentStudent();
                } catch (PDOException $e) {
                    $errorMessage = 'Database error: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'change_password') {
        // Get form data
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'All fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'New password and confirm password do not match';
        } elseif (strlen($newPassword) < 8) {
            $errorMessage = 'Password must be at least 8 characters long';
        } elseif (!password_verify($currentPassword, $student['password'])) {
            $errorMessage = 'Current password is incorrect';
        } else {
            // Update password
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $student['id']]);
                
                $successMessage = 'Password changed successfully';
            } catch (PDOException $e) {
                $errorMessage = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">My Profile</h1>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Personal Information</h2>
        
        <form method="POST" action="" class="profile-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="reg_number">Registration Number</label>
                <input type="text" id="reg_number" value="<?php echo htmlspecialchars($student['reg_number']); ?>" disabled>
                <small>Registration number cannot be changed</small>
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
    
    <div class="card">
        <h2>Change Password</h2>
        
        <form method="POST" action="" class="password-form">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>Password must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
    
    <div class="card">
        <h2>Booking Statistics</h2>
        
        <div class="stats-grid">
            <?php
            // Get booking statistics
            $stmt = $pdo->prepare("SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                                FROM bookings 
                                WHERE user_id = ?");
            $stmt->execute([$student['id']]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
    
    <div class="button-group">
        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>
</div>

<style>
    .page-title {
        font-size: 2rem;
        margin-bottom: 20px;
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }
    
    .alert-error {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }
    
    .profile-form, .password-form {
        max-width: 600px;
    }
    
    small {
        display: block;
        color: #666;
        margin-top: 5px;
        font-size: 0.8rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .stat-card {
        background-color: #f5f5f5;
        padding: 15px;
        border-radius: 4px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #4a6fdc;
    }
    
    .stat-label {
        margin-top: 5px;
        color: #666;
    }
    
    .button-group {
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>