<?php
// index.php
session_start();
require 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                // Fetch student_id to quickly use throughout portal
                $s_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
                $s_stmt->bind_param("i", $user['id']);
                $s_stmt->execute();
                $s_res = $s_stmt->get_result();
                if ($s_res->num_rows > 0) {
                    $_SESSION['student_id'] = $s_res->fetch_assoc()['id'];
                    header("Location: student_dashboard.php");
                } else {
                    $error = "Student profile not properly linked.";
                }
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System - Secure Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>Hostel Management System</h2>
        
        <?php if($error): ?>
            <p style="color: red; margin-bottom: 1rem;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php" style="margin-bottom: 2rem;">
            <p style="margin-bottom: 1rem; color: #666; font-weight: bold;">Secure Login</p>
            <div class="form-group" style="text-align: left;">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required placeholder="admin or student username">
            </div>
            <div class="form-group" style="text-align: left;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Your assigned password">
            </div>
            <button type="submit" class="btn btn-block">Login</button>
        </form>
        <p style="font-size: 0.8rem; color: #aaa;">Default Admin -> admin : admin123</p>
    </div>

</body>
</html>
