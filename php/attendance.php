<?php
// attendance.php: Handles attendance marking and fetching
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'employee') {
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $location = $_POST['location'] ?? 'Office';
    $action = $_POST['action'] ?? '';
    $now = date('H:i:s');
    $success = false;
    $msg = '';
    if ($action === 'in_time') {
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, in_time, location) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE in_time=VALUES(in_time), location=VALUES(location)");
        $stmt->bind_param('isss', $user_id, $today, $now, $location);
        $success = $stmt->execute();
        $stmt->close();
        $msg = $success ? 'IN-TIME logged!' : 'Failed to log IN-TIME.';
    } elseif ($action === 'out_time') {
        $stmt = $conn->prepare("UPDATE attendance SET out_time=?, location=? WHERE user_id=? AND date=?");
        $stmt->bind_param('ssis', $now, $location, $user_id, $today);
        $success = $stmt->execute();
        $stmt->close();
        $msg = $success ? 'OUT-TIME logged!' : 'Failed to log OUT-TIME.';
    } else {
        $msg = 'Invalid action.';
    }
    echo json_encode(['success' => $success, 'message' => $msg]);
    exit();
}
// For GET, you could add logic to fetch attendance records if needed.
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?> 