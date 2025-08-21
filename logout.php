<?php
session_start();

$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Clear all session variables
$_SESSION = [];

// Invalidate the session cookie if used
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Redirect based on role
if ($role === 'employee') {
	header('Location: login_employee.php');
} else {
	header('Location: login_admin.php');
}
exit();
?>
