<?php 
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $reg_number = $_POST["reg_number"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    try {
        // Check if the user already exists
        $check_sql = "SELECT * FROM users WHERE reg_number = :reg_number OR email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':reg_number', $reg_number);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            echo "<script>alert('User already exists! Please login.');</script>";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (first_name, last_name, reg_number, email, password) 
                    VALUES (:first_name, :last_name, :reg_number, :email, :password)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':reg_number', $reg_number);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            
            if ($stmt->execute()) {
                echo "<script>alert('Signup successful! Please check your email for verification.');</script>";
            }
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
    <title>Sign Up</title>
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
        <div class="left"></div>
        <div class="right">
            <img src="assets/images/logo.png" alt="Laikipia University Logo">
            <h2>Sign Up</h2>
            <form method="POST">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="reg_number" placeholder="Registration Number" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Sign Up</button>
            </form>
            <p>Already have an account? <a href="index.php">Login</a></p>
        </div>
    </div>
</body>
</html>
