<?php
// Ensure session is started
require_once 'config/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regNumber = isset($_POST['reg_number']) ? trim($_POST['reg_number']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate inputs
    if (empty($regNumber) || empty($password)) {
        $error = 'Please enter both registration number and password';
    } else {
        // Check if student exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reg_number = ?");
        $stmt->execute([$regNumber]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student && password_verify($password, $student['password'])) {
            // Login successful
            $_SESSION['student_id'] = $student['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid registration number or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            background: #222;
        }

        .container {
            display: flex;
            width: 80%;
            height: 80vh;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.2);
        }

        .left {
            width: 50%;
            background: url('assets/images/background.jpg') no-repeat center center/cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: white;
            text-align: center;
        }

        .left img {
            width: 150px;
            margin-bottom: 20px;
        }

        .right {
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .right h2 {
            margin-bottom: 20px;
            color: #333;
        }

        form {
            width: 100%;
            max-width: 350px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: darkred;
        }

        p {
            margin-top: 10px;
        }

        a {
            color: red;
            text-decoration: none;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left"></div>
        <div class="right">
            <img src="assets/images/logo.png" alt="Laikipia University Logo">
            <h1>Hi! Welcome</h1>
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="reg_number" placeholder="Registration Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
