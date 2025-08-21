<?php
// login_check.php: Handles login for both admin and employee
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$email || !$password || !$role) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param('ss', $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                if ($role === 'admin') {
                    header('Location: ../dashboard_admin.php');
                    exit();
                } else {
                    header('Location: ../dashboard_employee.php');
                    exit();
                }
            } else {
                $error = 'Incorrect email or password.';
            }
        } else {
            $error = 'Incorrect email or password.';
        }
        $stmt->close();
    }
    // Show error and go back to login page
    $redirect = ($role === 'admin') ? '../login_admin.php' : '../login_employee.php';
    $_SESSION['login_error'] = $error;
    header('Location: ' . $redirect);
    exit();
} else {
    header('Location: ../index.html');
    exit();
}
?> 