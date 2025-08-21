<?php
// dashboard_employee.php: Employee Dashboard
session_start();
require_once 'php/db.php';

date_default_timezone_set('Asia/Kolkata');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') {
    header('Location: login_employee.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Fetch personal info
$emp = [
    'name' => $_SESSION['user_name'],
    'designation' => '',
    'department' => '',
    'id' => $user_id,
    'join_date' => ''
];
$res = $conn->query("SELECT designation, department, join_date FROM employees WHERE email='$user_email' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $emp['designation'] = $row['designation'];
    $emp['department'] = $row['department'];
    $emp['join_date'] = $row['join_date'];
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$location = 'Office'; // Placeholder, can use JS for real location

// Handle IN-TIME/OUT-TIME
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['in_time'])) {
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, in_time, location) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE in_time=VALUES(in_time), location=VALUES(location)");
        $now = date('H:i:s');
        $stmt->bind_param('isss', $user_id, $today, $now, $location);
        $stmt->execute();
        $stmt->close();
        $msg = 'IN-TIME logged!';
    } elseif (isset($_POST['out_time'])) {
        $stmt = $conn->prepare("UPDATE attendance SET out_time=? WHERE user_id=? AND date=?");
        $now = date('H:i:s');
        $stmt->bind_param('sis', $now, $user_id, $today);
        $stmt->execute();
        $stmt->close();
        $msg = 'OUT-TIME logged!';
    }
}
// Fetch today's attendance
$attendance = $conn->query("SELECT in_time, out_time, location FROM attendance WHERE user_id=$user_id AND date='$today'")->fetch_assoc();

// Total leave taken
$res = $conn->query("SELECT COUNT(*) as total FROM leave_applications WHERE user_id=$user_id AND status='approved'");
$leave_taken = ($row = $res->fetch_assoc()) ? $row['total'] : 0;
// Missed attendance (absent days)
$res = $conn->query("SELECT COUNT(*) as total FROM (
    SELECT d FROM (
        SELECT DATE_SUB('$today', INTERVAL n DAY) as d FROM (
            SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
        ) days
    ) dates
    WHERE d < '$today' AND d >= DATE_SUB('$today', INTERVAL 7 DAY)
    AND NOT EXISTS (SELECT 1 FROM attendance WHERE user_id=$user_id AND date=d)
    AND NOT EXISTS (SELECT 1 FROM leave_applications WHERE user_id=$user_id AND status='approved' AND d BETWEEN start_date AND end_date)
) t");
$missed_attendance = ($row = $res->fetch_assoc()) ? $row['total'] : 0;
// Pending approvals
$res = $conn->query("SELECT COUNT(*) as total FROM leave_applications WHERE user_id=$user_id AND status='pending'");
$pending_approvals = ($row = $res->fetch_assoc()) ? $row['total'] : 0;
// Notices
$notices = [];
$res = $conn->query("SELECT title, description, published_date FROM notices ORDER BY published_date DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $notices[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f6f8fb;
            min-height: 100vh;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            margin: 0;
        }
        .header-bar {
            background: #fff;
            box-shadow: 0 2px 12px 0 rgba(31, 38, 135, 0.07);
            padding: 0.7rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e6f0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            color: #2d6cdf;
        }
        .brand {
            font-size: 1.35rem;
            font-weight: 700;
            color: #222;
        }
        .main-menu {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            background: #fff;
            box-shadow: 0 2px 12px 0 rgba(31, 38, 135, 0.07);
            padding: 0.7rem 0 0.7rem 0;
            margin-bottom: 2.2rem;
        }
        .menu-link {
            color: #2d6cdf;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
        }
        .menu-link:hover, .menu-link.active {
            background: #e6f0ff;
            color: #174a8b;
        }
        .dashboard-top {
            background: linear-gradient(90deg, #5b9df9 0%, #6a82fb 100%);
            padding: 2.2rem 0 1.5rem 0;
            color: #fff;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            margin-bottom: 2.5rem;
        }
        .dashboard-breadcrumb {
            font-size: 1rem;
            opacity: 0.85;
            margin-left: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-title {
            font-size: 2.1rem;
            font-weight: 700;
            margin-left: 2.5rem;
            margin-bottom: 1.2rem;
        }
        .main-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            max-width: 1200px;
            margin: 0 auto;
        }
        .left-panel {
            flex: 1.1;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2rem 1.2rem 2rem 1.2rem;
            min-width: 280px;
        }
        .profile-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .profile-info {
            font-size: 1.05rem;
            color: #222;
            margin-bottom: 0.7rem;
        }
        .profile-label {
            color: #888;
            font-size: 0.97rem;
            margin-right: 0.5rem;
        }
        .attendance-panel {
            flex: 2.2;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2rem 2rem 2rem 2rem;
            margin-bottom: 2rem;
        }
        .attendance-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .attendance-btns {
            display: flex;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .btn {
            background: linear-gradient(90deg, #2d6cdf 0%, #5b9df9 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.85rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(45, 108, 223, 0.08);
        }
        .btn:disabled {
            background: #b3c6e6;
            cursor: not-allowed;
        }
        .msg {
            background: #e0ffe0;
            color: #1a7f1a;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid #b2e6b2;
        }
        .right-panel {
            flex: 1.1;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2rem 1.2rem 2rem 1.2rem;
            min-width: 280px;
        }
        .right-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .right-stat {
            font-size: 1.05rem;
            color: #222;
            margin-bottom: 0.7rem;
        }
        .notice-board {
            margin-top: 1.5rem;
        }
        .notice-title {
            font-size: 1.08rem;
            color: #2d6cdf;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .notice-item {
            background: #f0f4fa;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            margin-bottom: 0.7rem;
        }
        .notice-date {
            color: #888;
            font-size: 0.93rem;
            margin-bottom: 0.2rem;
        }
        @media (max-width: 1100px) {
            .main-content {
                flex-direction: column;
            }
            .right-panel {
                margin-top: 2rem;
            }
        }
        @media (max-width: 700px) {
            .main-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header-left">
            <div class="logo">HR</div>
            <span class="brand">HR Management</span>
        </div>
    </div>
    <nav class="main-menu">
        <a href="dashboard_employee.php" class="menu-link active">Dashboard</a>
        <a href="employee/leave_employee.php" class="menu-link">Leave Application</a>
        <a href="employee/request_employee.php" class="menu-link">Request Application</a>
        <a href="employee/directory.php" class="menu-link">Employee Directory</a>
        <a href="logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="dashboard-top">
        <div class="dashboard-breadcrumb">Dashboard &gt; Attendance</div>
        <div class="dashboard-title">Dashboard</div>
    </div>
    <div class="main-content">
        <div class="left-panel">
            <div class="profile-title">Personal Info</div>
            <div class="profile-info"><span class="profile-label">Name:</span> <?php echo htmlspecialchars($emp['name']); ?></div>
            <div class="profile-info"><span class="profile-label">Designation:</span> <?php echo htmlspecialchars($emp['designation']); ?></div>
            <div class="profile-info"><span class="profile-label">Department:</span> <?php echo htmlspecialchars($emp['department']); ?></div>
            <div class="profile-info"><span class="profile-label">Employee ID:</span> <?php echo htmlspecialchars($emp['id']); ?></div>
            <div class="profile-info"><span class="profile-label">Join Date:</span> <?php echo htmlspecialchars($emp['join_date']); ?></div>
        </div>
        <div class="attendance-panel">
            <div class="attendance-title">Attendance</div>
            <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
            <form method="post" class="attendance-btns">
                <button type="submit" name="in_time" class="btn" <?php echo isset($attendance['in_time']) ? 'disabled' : ''; ?>>IN-TIME</button>
                <button type="submit" name="out_time" class="btn" <?php echo (empty($attendance['in_time']) || isset($attendance['out_time'])) ? 'disabled' : ''; ?>>OUT-TIME</button>
            </form>
            <div class="profile-info"><span class="profile-label">Today's In Time:</span> <?php echo $attendance['in_time'] ?? '-'; ?></div>
            <div class="profile-info"><span class="profile-label">Today's Out Time:</span> <?php echo $attendance['out_time'] ?? '-'; ?></div>
            <div class="profile-info"><span class="profile-label">Location:</span> <?php echo $attendance['location'] ?? '-'; ?></div>
        </div>
        <div class="right-panel">
            <div class="right-title">Quick Stats</div>
            <div class="right-stat"><span class="profile-label">Total Leave Taken:</span> <?php echo $leave_taken; ?></div>
            <div class="right-stat"><span class="profile-label">Missed Attendance (last 7d):</span> <?php echo $missed_attendance; ?></div>
            <div class="right-stat"><span class="profile-label">Pending Approvals:</span> <?php echo $pending_approvals; ?></div>
            <div class="notice-board">
                <div class="notice-title">Notice Board</div>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice-item">
                        <div class="notice-date"><?php echo htmlspecialchars($notice['published_date']); ?></div>
                        <div><b><?php echo htmlspecialchars($notice['title']); ?></b></div>
                        <div><?php echo htmlspecialchars($notice['description']); ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($notices)): ?>
                    <div class="notice-item">No active notices.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 