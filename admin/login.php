<?php
// Ensure session is started at the top
require_once '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        require_once 'includes/auth.php';

        if (function_exists('adminLogin')) { // Ensure function exists before calling
            if (adminLogin($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Authentication function missing!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Student Venue Finder</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 350px;
            text-align: center;
        }

        .logo {
            width: 250px;
            margin-bottom: 10px;
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px;
            background-color: #d32f2f;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #b71c1c;
        }

        .forgot-password {
            display: block;
            margin-top: 10px;
            color: #d32f2f;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logo.png" alt="Laikipia University" class="logo">
        <h2>Admin Login</h2>
        
        <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" required>
            
            <label for="password">Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
        
    </div>
</body>
</html>
