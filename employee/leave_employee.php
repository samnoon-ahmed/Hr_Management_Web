<?php
// leave_employee.php: Employee Leave Application
session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') {
    header('Location: ../login_employee.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$msg = '';

// Handle leave application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reason'], $_POST['start_date'], $_POST['end_date'])) {
    $reason = trim($_POST['reason']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    if ($reason && $start_date && $end_date && $start_date <= $end_date) {
        $stmt = $conn->prepare("INSERT INTO leave_applications (user_id, reason, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param('isss', $user_id, $reason, $start_date, $end_date);
        if ($stmt->execute()) {
            $msg = 'Leave application submitted!';
        } else {
            $msg = 'Failed to submit leave application.';
        }
        $stmt->close();
    } else {
        $msg = 'Please fill all fields and ensure dates are valid.';
    }
}
// Fetch leave history
$leaves = [];
$res = $conn->query("SELECT reason, start_date, end_date, status FROM leave_applications WHERE user_id=$user_id ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
    $leaves[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Application (Employee)</title>
    <style>
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
        @media (max-width: 700px) {
            .main-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
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
        .leave-container {
            max-width: 600px;
            margin: 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .leave-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .leave-form {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            margin-bottom: 2rem;
        }
        .leave-form label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.3rem;
        }
        .leave-form input, .leave-form textarea {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.2s;
        }
        .leave-form input:focus, .leave-form textarea:focus {
            outline: none;
            border: 1.5px solid #2d6cdf;
            background: #fff;
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
        .btn:hover {
            background: linear-gradient(90deg, #1b4fa0 0%, #3576c9 100%);
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
        .leave-history-title {
            font-size: 1.1rem;
            color: #2d6cdf;
            font-weight: 600;
            margin-bottom: 0.7rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            padding: 0.7rem 0.5rem;
            text-align: left;
        }
        th {
            background: #f0f4fa;
            color: #2d6cdf;
            font-weight: 600;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }
        .status-approved {
            color: #1bbf5c;
            font-weight: 600;
        }
        .status-pending {
            color: #f39c12;
            font-weight: 600;
        }
        .status-cancelled {
            color: #c00;
            font-weight: 600;
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
        <a href="../dashboard_employee.php" class="menu-link">Dashboard</a>
        <a href="leave_employee.php" class="menu-link active">Leave Application</a>
        <a href="request_employee.php" class="menu-link">Request Application</a>
        <a href="directory.php" class="menu-link">Employee Directory</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="dashboard-top">
        <div class="dashboard-breadcrumb">Dashboard &gt; Leave Application</div>
        <div class="dashboard-title">Leave Application</div>
    </div>
    <div class="leave-container">
        <div class="leave-title">Leave Application</div>
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <form method="post" class="leave-form">
            <div>
                <label>Reason</label>
                <textarea name="reason" rows="2" required></textarea>
            </div>
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            <div>
                <label>End Date</label>
                <input type="date" name="end_date" required>
            </div>
            <button type="submit" class="btn">Submit Leave</button>
        </form>
        <div class="leave-history-title">Your Leave Applications</div>
        <table>
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                <tr>
                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                    <td class="status-<?php echo strtolower($leave['status']); ?>"><?php echo ucfirst($leave['status']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($leaves)): ?>
                <tr><td colspan="4">No leave applications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 