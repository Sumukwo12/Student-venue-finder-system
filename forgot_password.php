<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $last_name = $_POST["last_name"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    try {
        // Check if email and last name exist in DB
        $sql = "SELECT * FROM users WHERE email = :email AND last_name = :last_name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            // Update password
            $update_sql = "UPDATE users SET password = :new_password WHERE email = :email";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':new_password', $new_password);
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                echo "<script>alert('Password changed successfully! You can now log in.'); window.location='index.php';</script>";
            } else {
                echo "<script>alert('Error updating password! Try again.');</script>";
            }
        } else {
            echo "<script>alert('Invalid email or last name!');</script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General styling */
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

        /* Split Screen Layout */
        .container {
            display: flex;
            width: 80%;
            height: 80vh;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.2);
        }

        /* Left Side */
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

        /* Right Side (Forms) */
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
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
        </div>
        <div class="right">
            <img src="assets/images/logo.png" alt="Laikipia University Logo">
            <h2>Reset Password</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="Registered Email" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <button type="submit">Reset Password</button>
            </form>
            <p><a href="index.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
